<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Survey;
use App\Models\Video;
use App\Models\Order;
use App\Models\UserAnswer; // Ensure you have a UserAnswer model
use App\Models\UserAnswerImage;
use App\Models\UserSurvey;
use App\Models\QuickOrder;
use App\Models\Variable;
use App\Http\Controllers\Api\OrderController;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\EncodedImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class UserAnswerController extends Controller
{

    public function SetUserAnswerImage(Request $request)
    {
        // Проверка, что данные приходят
        // dd($request->all());

        // Валидация данных
        $validatedData = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        // Получение файла
        $image = $request->file('image');

        // Сохранение файла в storage/app/public/user_answers/images
        $path = $image->store('user_answers/images', 'public');

        // Сохранение пути в базу данных
        $userAnswerImage = UserAnswerImage::create([
            'path' => "storage/$path", // Сохранение пути в формате 'storage/user_answers/images/filename.jpg'
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully',
            'image_path' => asset("storage/$path") // Генерация полного URL
        ], Response::HTTP_CREATED);
    }

    public function GetUserAnswersByVideoID(Request $request)
    {
        // Получаем параметры из запроса
        $survey_id = $request->survey_id ?? 1;
        $video_id = $request->video_id;
        $user_id = $request->user_id ?? auth()->id(); // Если user_id не передан, используем auth()->id()
        // Загружаем survey с ID = 1
        $survey = Survey::with(['questions'])->where('id', $survey_id)->first();

        if (!$survey) {
            return response()->json(['message' => 'Survey not found'], Response::HTTP_NOT_FOUND);
        }

        // Загружаем UserAnswers для указанного video_id
        $user_survey = UserSurvey::where('video_id', $video_id)
//            ->where('survey_id', $survey_id)
//            ->where('user_id', $user_id)
            ->first();
        $userAnswers = UserAnswer::where('user_survey_id',$user_survey->id)
            ->get();

        // Обрабатываем questions, добавляя только связанные options, custom_value и image_urls
        $survey->questions->each(function ($question) use ($userAnswers) {
            // Получаем UserAnswer для текущего вопроса
            $relatedAnswer = $userAnswers->firstWhere('question_id', $question->id);

            if ($relatedAnswer) {
                // Фильтруем options только по указанным в option_ids
                $optionIds = json_decode($relatedAnswer->option_ids, true);
                $question->options = $question->options()->whereIn('id', $optionIds)->get();

                // Добавляем custom_value
                $question->custom_value = $relatedAnswer->custom_value;
                // Добавляем image_urls (если есть)
                $question->image_urls = $relatedAnswer->image_urls ? json_decode($relatedAnswer->image_urls, true) : null;
            } else {
                // Если UserAnswer отсутствует, очищаем options, custom_value и image_urls
                $question->options = [];
                $question->custom_value = null;
                $question->image_urls = null;
            }
        });

        return response()->json($survey, Response::HTTP_OK);
    }

    public function index()
    {
        // Загружаем данные с опциями
        $userAnswers = UserAnswer::all()->map(function ($userAnswer) {
//            dd();
            $userAnswer->options = $userAnswer->options(); // Загружаем вручную через метод options()
            return $userAnswer;
        });

        return response()->json($userAnswers, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        // Валидация данных
        $validatedData = Validator::make($request->all(), [
            'survey_id' => 'required|exists:surveys,id',
            'video_id' => 'nullable|exists:videos,id',
            'answers' => 'required',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.option_ids' => 'nullable|array',
            'answers.*.option_ids.*' => 'integer|exists:options,id',
            'answers.*.custom_value' => 'nullable|string|max:255',
            'answers.*.image_urls' => 'nullable|array', // Новый параметр для image_urls
            'answers.*.image_urls.*' => 'nullable|string', // Проверка, что это изображения
        ]);

        // Проверка уникальности комбинации survey_id, video_id, question_id и master_id
        $validatedData->after(function ($validator) use ($request) {
            $exists = UserSurvey::where('survey_id', $request->survey_id)
                ->whereNotNull('video_id')
                ->where('video_id', $request->video_id)
                ->where('user_id', auth()->id())
                ->exists();

            if ($exists) {
                $validator->errors()->add('survey_id', 'Такая комбинация survey_id, video_id и user_id уже существует.');
            }
        });

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $validatedData = $validatedData->validated();

        $user_survey = UserSurvey::create([
            'survey_id' => $validatedData['survey_id'],
            'video_id' => $validatedData['video_id'],
            'user_id' => auth()->id(), // Или другой источник master_id
        ]);
        // Перебираем ответы

        foreach (json_decode($request->answers,true) as $answer) {
            // Сохраняем ответ в базе данных
            UserAnswer::create([
                'user_survey_id' => $user_survey->id,
                'question_id' => $answer['question_id'],
                'option_ids' => isset($answer['option_ids']) ? json_encode($answer['option_ids']) : null,
//                'custom_value' => isset($answer['custom_value']) ? json_encode($answer['custom_value']) : null,
                'custom_value' => $answer['custom_value'] ?? null,
                'image_urls' => isset($answer['image_urls'])
                    ? json_encode($answer['image_urls'], JSON_UNESCAPED_UNICODE)
                    : null,
                ]);
        }

        if (!is_null($validatedData['video_id'])){
            $video = Video::where('id',$validatedData['video_id'])
                ->first();
            // 🔁 Создание связанного Order
            $order = Order::create([
                'user_id' => auth()->id(),
                'user_survey_id' => $user_survey->id,
                'master_id' => $video->user_id,
                'video_id' => $validatedData['video_id'],
                'status_id' => 1,
            ]);
        }
        else{
            $quickOrder = QuickOrder::create([
                'user_id' => auth()->id(),
                'group_iter' => 0,
                'refresh_time' => Carbon::now(),
                'responded' => false,
            ]);
            UserSurvey::where('id',$user_survey->id)
                ->update([
                    'quick_order_id'=>$quickOrder->id,
                ]);

            // Отправляем заказы мастерам
            app(OrderController::class)->SendQuickOrdersByUserId($quickOrder->user_id);
        }



        return response()->json(['message' => 'Answers saved successfully','user_survey_id'=>$user_survey->id], Response::HTTP_CREATED);
    }

    public function AddUserAnswersWithImages(Request $request)
    {
        // Если answers — строка, пытаемся превратить в массив
        if (is_string($request->answers)) {
            $decoded = json_decode($request->answers, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'message' => 'Invalid JSON in "answers".',
                    'error' => json_last_error_msg(),
                ], 400);
            }

            $request->merge(['answers' => $decoded]);
        }

//        print_r($request->all());
        $validated = Validator::make($request->all(), [
            'survey_id' => 'required|exists:surveys,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'region_id' => 'nullable|integer|exists:regions,id',
            'country_id' => 'nullable|integer|exists:countries,id',
            'video_id' => 'nullable|exists:videos,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.option_ids' => 'nullable|array',
//            'answers.*.option_ids.*' => 'integer',
            'answers.*.option_ids.*' => 'integer|exists:options,id',
            'answers.*.custom_value' => 'nullable|string|max:255',
            'answers.*.image_urls' => 'nullable|array',
            'answers.*.image_urls.*' => 'nullable|string|starts_with:data:image/',
        ]);
        // Проверка уникальности комбинации survey_id, video_id, question_id и master_id
        $validated->after(function ($validator) use ($request) {
            $exists = UserSurvey::where('survey_id', $request->survey_id)
                ->whereNotNull('video_id')
                ->where('video_id', $request->video_id)
                ->where('user_id', auth()->id())
                ->exists();

            if ($exists) {
                $validator->errors()->add('survey_id', 'Такая комбинация survey_id, video_id и user_id уже существует.');
            }
        });

        if ($validated->fails()) {
            $errorText = implode("\n", $validated->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $data = $validated->validated();

        // Создаем запись UserSurvey
        $userSurvey = UserSurvey::create([
            'survey_id' => $data['survey_id'],
            'video_id' => Arr::get($data, 'video_id', null),
            'user_id' => auth()->id(),
        ]);

        foreach ($data['answers'] as $answer) {
            $uploadedImages = [];

            // Если есть картинки
            if (!empty($answer['image_urls'])) {
                foreach ($answer['image_urls'] as $base64) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                        $extension = strtolower($matches[1]); // jpg, png и т.д.
                        $base64 = substr($base64, strpos($base64, ',') + 1);
                        $base64 = base64_decode($base64);

                        $filename = uniqid() . '.' . $extension;
                        $path = "user_answers/images/{$filename}";

                        Storage::disk('public')->put($path, $base64);
                        $uploadedImages[] = 'storage/'.$path;
                    }
                }
//                print_r($uploadedImages);
            }

//            dd($uploadedImages);
            // Сохраняем ответ
            UserAnswer::create([
                'user_survey_id' => $userSurvey->id,
                'question_id' => $answer['question_id'],
                'option_ids' => isset($answer['option_ids']) ? json_encode($answer['option_ids']) : null,
                'custom_value' => $answer['custom_value'] ?? null,
                'image_urls' => !empty($uploadedImages) ? json_encode($uploadedImages, JSON_UNESCAPED_UNICODE) : null,
            ]);
        }
//        dd($data);
        if (!is_null(Arr::get($data, 'video_id', null))){
            $video = Video::where('id',Arr::get($data, 'video_id', null))
                ->first();
            // 🔁 Создание связанного Order
            $order = Order::create([
                'user_id' => auth()->id(),
                'user_survey_id' => $userSurvey->id,
                'master_id' => $video->user_id,
                'video_id' => Arr::get($data, 'video_id', null),
                'status_id' => 1,
            ]);
        }
        else{
            $quickOrder = QuickOrder::create([
                'user_id' => auth()->id(),
                'user_survey_id' => $userSurvey->id,
                'group_iter' => 0,
                'refresh_time' => Carbon::now(),
                'responded' => false,
                'city_id' => Arr::get($data, 'city_id', 66),
                'region_id' => Arr::get($data, 'region_id', 4),
                'country_id' => Arr::get($data, 'country_id', 1),
                'masters_left' => Variable::where('id',14)->first()['value'],
            ]);

            UserSurvey::where('id',$userSurvey->id)
                ->update([
                    'quick_order_id'=>$quickOrder->id,
                ]);

            // Отправляем заказы мастерам
//            app(OrderController::class)->SendQuickOrdersByUserId($quickOrder->user_id);
        }

        return response()->json([
            'message' => 'Answers with images saved successfully',
            'user_survey_id' => $userSurvey->id,
        ], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $userAnswer = UserAnswer::find($id);

        if (!$userAnswer) {
            return response()->json(['message' => 'User answer not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($userAnswer, Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        // Валидация входных данных
        $validatedData = Validator::make($request->all(), [
            'survey_id' => 'required|exists:surveys,id',
            'video_id' => 'nullable|exists:videos,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.option_ids' => 'nullable|array',
            'answers.*.option_ids.*' => 'integer|exists:options,id',
            'answers.*.custom_value' => 'nullable|string|max:255',
            'answers.*.image_urls' => 'nullable|array',
            'answers.*.image_urls.*' => 'nullable|image',
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $validatedData = $validatedData->validated();

        // Поиск существующего UserSurvey
        $userSurvey = UserSurvey::find($id);

        if (!$userSurvey) {
            return response()->json(['error' => 'UserSurvey not found'], Response::HTTP_NOT_FOUND);
        }

        // Обновление данных UserSurvey
        $userSurvey->update([
            'survey_id' => $validatedData['survey_id'],
            'video_id' => $validatedData['video_id'],
            'master_id' => auth()->id(),
        ]);

        // Обновление ответов
        foreach ($validatedData['answers'] as $answer) {
            // Сжимаем изображения, если они переданы
            if (isset($answer['image_urls'])) {
                $compressedImagePaths = [];

                foreach ($answer['image_urls'] as $image) {
                    $originalPath = $image->store('photos/user_answers', 'public');
                    $originalFullPath = storage_path('app/public/' . $originalPath);

                    if (File::exists($originalFullPath)) {
                        File::delete($originalFullPath);
                    }

                    $compressedImagePaths[] = $originalPath;
                }

                $answer['image_urls'] = json_encode($compressedImagePaths);
            }

            // Найти существующий ответ или создать новый
            UserAnswer::updateOrCreate(
                [
                    'user_survey_id' => $userSurvey->id,
                    'question_id' => $answer['question_id'],
                ],
                [
                    'option_ids' => isset($answer['option_ids']) ? json_encode($answer['option_ids']) : null,
                    'custom_value' => $answer['custom_value'] ?? null,
                    'image_urls' => $answer['image_urls'] ?? null,
                ]
            );
        }

        return response()->json(['message' => 'Survey and answers updated successfully'], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $userAnswer = UserAnswer::find($id);

        if (!$userAnswer) {
            return response()->json(['message' => 'User answer not found'], Response::HTTP_NOT_FOUND);
        }

        $userAnswer->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
