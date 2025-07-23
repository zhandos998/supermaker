<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DefaultAnswer; // Ensure you have a DefaultAnswer model
use App\Models\DefaultSurvey;
use App\Models\Question;
use App\Models\Survey;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class DefaultAnswerController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/default_answers/GetDefaultAnswersByVideoID",
     *     tags={"DefaultAnswers"},
     *     security={{"sanctum": {}}},
     *     summary="Get default answers by video ID",
     *     operationId="getDefaultAnswersByVideoID",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="video_id", type="integer", description="ID of the video", example=1),
     *             @OA\Property(property="user_id", type="integer", description="ID of the user (optional)"),
     *             @OA\Property(property="survey_id", type="integer", description="ID of the survey", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Survey with default answers fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="questions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="question_text", type="string"),
     *                     @OA\Property(
     *                         property="options",
     *                         type="array",
     *                         @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="option_text", type="string"))
     *                     ),
     *                     @OA\Property(property="custom_value", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Survey or video not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Survey not found")
     *         )
     *     )
     * )
     */
    public function GetDefaultAnswersByVideoID(Request $request)
    {
        // Получаем параметры из запроса
        $survey_id = $request->survey_id ?? 1; // Используем 1 как дефолтное значение для survey_id
        $video_id = $request->video_id;
        $user_id = $request->user_id;
        // Загружаем survey с ID = 1
        $survey = Survey::with(['questions'])->where('id', $survey_id)->first();

        if (!$survey) {
            return response()->json(['message' => 'Survey not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$user_id){
            $video_user_id = Video::find($video_id)->user_id;

            if (!$video_user_id) {
                return response()->json(['message' => 'Video not found'], Response::HTTP_NOT_FOUND);
            }
            $user_id = $video_user_id;
        }

        // Загружаем DefaultAnswers для указанного video_id
        $defaultSurveys = DefaultSurvey::where('video_id', $video_id)
            ->where('master_id', $user_id)
            ->where('survey_id', $survey_id)
            ->first();
        $defaultAnswers = DefaultAnswer::where('default_survey_id', $defaultSurveys->id)
//            ->where('master_id', $user_id)
//            ->where('survey_id', $survey_id)
            ->get();

        // Обрабатываем questions, добавляя только связанные options и custom_value
        $survey->questions->each(function ($question) use ($defaultAnswers) {
            // Получаем DefaultAnswer для текущего вопроса
            $relatedAnswer = $defaultAnswers->firstWhere('question_id', $question->id);

            if ($relatedAnswer) {
                // Фильтруем options только по указанным в option_ids
                $optionIds = json_decode($relatedAnswer->option_ids, true);
                $question->options = $question->options()->whereIn('id', $optionIds)->get();

                // Добавляем custom_value
                $question->custom_value = $relatedAnswer->custom_value;
            } else {
                // Если DefaultAnswer отсутствует, очищаем options и custom_value
                $question->options = [];
                $question->custom_value = null;
            }
        });

        return response()->json($survey, Response::HTTP_OK);
    }



    /**
     * @OA\Get(
     *     path="/api/default_answers",
     *     tags={"DefaultAnswers"},
     *     security={{"sanctum": {}}},
     *     summary="Get a list of default answers",
     *     description="Returns a list of all default answers.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        // $defaultAnswers = DefaultAnswer::with('question')->get();
        $defaultAnswers = DefaultAnswer::all();
        return response()->json($defaultAnswers, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/default_answers",
     *     tags={"DefaultAnswers"},
     *     security={{"sanctum": {}}},
     *     summary="Store answers with images",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"survey_id", "answers"},
     *                 @OA\Property(property="survey_id", type="integer", example="1"),
     *                 @OA\Property(property="video_id", type="integer", example="38"),
     *                 @OA\Property(
     *                     property="answers",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         required={"question_id"},
     *                         @OA\Property(property="question_id", type="integer"),
     *                         @OA\Property(property="option_ids", type="array", @OA\Items(type="integer")),
     *                         @OA\Property(property="custom_value", type="string"),
     *                         @OA\Property(
     *                             property="image_urls",
     *                             type="array",
     *                             @OA\Items(type="string",
     *                             format="binary", description="Image file", example="image files"),
     *                             description="List of image files"
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Answers successfully saved",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Answers saved successfully")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        // return json_decode($request->answers,true);
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
            'answers.*.image_urls.*' => 'nullable|image', // Проверка, что это изображения
        ]);

        // dd($request->all());
        // Проверка уникальности комбинации survey_id, video_id, question_id и master_id
        $validatedData->after(function ($validator) use ($request) {
            // dd($request->answers);
            foreach (json_decode($request->answers,true) as $answer) {
                // return $answer;
                // dd($answer);
                $exists = DefaultSurvey::where('survey_id', $request->survey_id)
                    ->where('video_id', $request->video_id)
                    // ->where('question_id', $answer['question_id'])
                    // ->where('question_id', $answer->question_id)
                    ->where('master_id', auth()->id())
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('survey_id', 'Такая комбинация survey_id, video_id и master_id уже существует.');
                }
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


        $default_survey = DefaultSurvey::create([
            'survey_id' => $validatedData['survey_id'],
            'video_id' => $validatedData['video_id'],
            'master_id' => auth()->id(), // Или другой источник master_id
        ]);
        // Перебираем ответы
        foreach (json_decode($validatedData['answers'],true) as $answer) {
            // Сжимаем изображения (если они есть)
            if (isset($answer['image_urls'])) {
                $compressedImagePaths = [];

                foreach ($answer['image_urls'] as $image) {
                    // Сохраняем оригинальное изображение
                    $originalPath = $image->store('photos/default_answers', 'public');
                    $originalFullPath = storage_path('app/public/' . $originalPath);

                    // Сжимаем изображение
                    // $compressedPath = 'photos/default_answers/compressed/' . pathinfo($originalPath, PATHINFO_FILENAME) . '-compressed.' . $image->getClientOriginalExtension();
                    // $compressedFullPath = storage_path('app/public/' . $compressedPath);

                    // Создаем новый объект ImageManager и сжимаем изображение
                    // $manager = new ImageManager(Driver::class);
                    // $imageObj = $manager->read($originalFullPath);
                    // $imageObj->save($compressedFullPath, 50); // Сжатие до 50%

                    // Удаляем оригинальное изображение
                    if (File::exists($originalFullPath)) {
                        File::delete($originalFullPath);
                    }

                    // Добавляем путь к сжатыми изображениям в массив
                    $compressedImagePaths[] = $originalPath;
                }

                // Преобразуем массив сжимаемых изображений в строку JSON
                $answer['image_urls'] = json_encode($compressedImagePaths);
            }
            // Сохраняем ответ в базе данных
            DefaultAnswer::create([
                'default_survey_id' => $default_survey->id,
                'question_id' => $answer['question_id'],
                'option_ids' => isset($answer['option_ids']) ? json_encode($answer['option_ids']) : null,
                'custom_value' => $answer['custom_value'] ?? null,
                'image_urls' => $answer['image_urls'] ?? null, // Сохраняем сжатые изображения
            ]);
        }

        return response()->json(['message' => 'Answers saved successfully','default_survey_id'=>$default_survey->id], Response::HTTP_CREATED);
    }

    // public function store(Request $request)
    // {
    //     // print_r($request->all());
    //     $validatedData = Validator::make($request->all(), [
    //         'question_id' => 'required|exists:questions,id',
    //         'survey_id' => 'required|exists:surveys,id',
    //         'video_id' => 'required|exists:videos,id',
    //         // 'master_id' => 'required|exists:users,id',
    //         'option_ids' => 'nullable|array',
    //         'option_ids.*' => 'integer|exists:options,id', // Опционально, если есть связи с вариантами
    //         'custom_value' => 'nullable|string|max:255',
    //     ]);
    //     // Проверка уникальности
    //     $validatedData->after(function ($validator) use ($request) {
    //         $exists = DefaultAnswer::where('survey_id', $request->survey_id)
    //             ->where('video_id', $request->video_id)
    //             ->where('question_id', $request->question_id)
    //             ->where('master_id', auth()->id())
    //             ->exists();
    //         if ($exists) {
    //             $validator->errors()->add('survey_id', 'Такая комбинация survey_id, video_id, question_id и master_id уже существует.');
    //         }
    //     });
    //     if ($validatedData->fails()) {
    //         return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
    //     }
    //     $validatedData = $validatedData->validated();
    //     $validatedData['master_id'] = auth()->id();
    //     $defaultAnswer = DefaultAnswer::create($validatedData);
    //     return response()->json($defaultAnswer, Response::HTTP_CREATED);
    // }

    /**
     * @OA\Post(
     *     path="/api/default_answers/AddDefaultAnswersWithImages",
     *     tags={"DefaultAnswers"},
     *     security={{"sanctum": {}}},
     *     summary="Store answers with images",
     *     operationId="storeAnswersWithImages",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"survey_id", "answers"},
     *                 @OA\Property(property="survey_id", type="integer", example=1),
     *                 @OA\Property(property="video_id", type="integer", example=107),
     *                 @OA\Property(
     *                     property="answers",
     *                     type="array",
     *                     description="Array of answers",
     *                     @OA\Items(
     *                         type="object",
     *                         required={"question_id"},
     *                         @OA\Property(property="question_id", type="integer", example=2),
     *                         @OA\Property(property="option_ids", type="array", @OA\Items(type="integer"), example={2}),
     *                         @OA\Property(property="custom_value", type="string", example="string"),
     *                         @OA\Property(property="image_urls", type="array", @OA\Items(type="string"), example={
     *                             "storage/user_answers/images/xfrNqETCemFCWUjMiLSkfamrQmBqVuRyMKfeRsG9.png"
     *                         })
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Answers successfully saved",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Answers saved successfully")
     *         )
     *     )
     * )
     */
    public function AddDefaultAnswersWithImages(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'survey_id' => 'required|exists:surveys,id',
            'video_id' => 'nullable|exists:videos,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.option_ids' => 'nullable|array',
            'answers.*.option_ids.*' => 'integer|exists:options,id',
            'answers.*.custom_value' => 'nullable|string|max:255',
            'answers.*.image_urls' => 'nullable|array',
            'answers.*.image_urls.*' => 'nullable|file|image|max:5120', // до 5MB на фото
        ]);

        if ($validated->fails()) {
            $errorText = implode("\n", $validated->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $data = $validated->validated();

        // Создаем запись DefaultSurvey
        $defaultSurvey = DefaultSurvey::create([
            'survey_id' => $data['survey_id'],
            'video_id' => $data['video_id'],
            'master_id' => auth()->id(),
        ]);

        foreach ($data['answers'] as $answer) {
            $uploadedImages = [];

            // Если есть картинки
            if (!empty($answer['image_urls'])) {
                foreach ($answer['image_urls'] as $image) {
                    $path = $image->store('default_answers/photos', 'public');
                    $uploadedImages[] = $path;
                }
            }

            // Сохраняем ответ
            DefaultAnswer::create([
                'default_survey_id' => $defaultSurvey->id,
                'question_id' => $answer['question_id'],
                'option_ids' => isset($answer['option_ids']) ? json_encode($answer['option_ids']) : null,
                'custom_value' => $answer['custom_value'] ?? null,
                'image_urls' => !empty($uploadedImages) ? json_encode($uploadedImages, JSON_UNESCAPED_UNICODE) : null,
            ]);
        }

        return response()->json([
            'message' => 'Answers with images saved successfully',
            'default_survey_id' => $defaultSurvey->id,
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/default_answers/{id}",
     *     tags={"DefaultAnswers"},
     *     security={{"sanctum": {}}},
     *     summary="Get a default answer by ID",
     *     description="Returns a single default answer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the default answer",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Default answer not found")
     * )
     */
    public function show($id)
    {
        $defaultAnswer = DefaultAnswer::with('question')->find($id);

        if (!$defaultAnswer) {
            return response()->json(['message' => 'Default answer not found'], Response::HTTP_NOT_FOUND);
        }

        $options = $defaultAnswer->options();

        // Формируем ответ
        return response()->json([
            'defaultAnswer' => $defaultAnswer,
            'options' => $options,
        ], Response::HTTP_OK);
        // return response()->json($defaultAnswer, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/default_answers/{id}",
     *     tags={"DefaultAnswers"},
     *     security={{"sanctum": {}}},
     *     summary="Update default answers",
     *     description="Updates existing default answers for a given survey and video",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the DefaultSurvey to be updated",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"survey_id", "answers"},
     *             @OA\Property(property="survey_id", type="integer", example="1", description="ID of the related survey"),
     *             @OA\Property(property="video_id", type="integer", nullable=true, description="ID of the related video"),
     *             @OA\Property(property="answers", type="array", description="Array of answers to be updated",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"question_id"},
     *                     @OA\Property(property="question_id", type="integer", description="ID of the related question"),
     *                     @OA\Property(
     *                         property="option_ids",
     *                         type="array",
     *                         @OA\Items(type="integer"),
     *                         nullable=true,
     *                         description="Array of selected option IDs"
     *                     ),
     *                     @OA\Property(property="custom_value", type="string", nullable=true, description="Custom answer value (if any)"),
     *                     @OA\Property(
     *                         property="image_urls",
     *                         type="array",
     *                         nullable=true,
     *                         description="Array of image files (optional)",
     *                         @OA\Items(type="string", format="binary")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Default answers updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Survey and answers updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="DefaultSurvey not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="DefaultSurvey not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to validation errors or incorrect data",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={"survey_id": {"The survey_id field is required."}})
     *         )
     *     )
     * )
     */
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

        // Поиск существующего DefaultSurvey
        $defaultSurvey = DefaultSurvey::find($id);

        if (!$defaultSurvey) {
            return response()->json(['error' => 'DefaultSurvey not found'], Response::HTTP_NOT_FOUND);
        }

        // Обновление данных DefaultSurvey
        $defaultSurvey->update([
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
                    $originalPath = $image->store('photos/default_answers', 'public');
                    $originalFullPath = storage_path('app/public/' . $originalPath);

                    if (File::exists($originalFullPath)) {
                        File::delete($originalFullPath);
                    }

                    $compressedImagePaths[] = $originalPath;
                }

                $answer['image_urls'] = json_encode($compressedImagePaths);
            }

            // Найти существующий ответ или создать новый
            DefaultAnswer::updateOrCreate(
                [
                    'default_survey_id' => $defaultSurvey->id,
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

    // public function update(Request $request, $video_id)
    // {
    //     // Валидация данных
    //     $validatedData = Validator::make($request->all(), [
    //         'survey_id' => 'required|exists:surveys,id',
    //         'answers' => 'required|array',
    //         'answers.*.question_id' => 'required|exists:questions,id',
    //         'answers.*.option_ids' => 'nullable|array',
    //         'answers.*.option_ids.*' => 'integer|exists:options,id',
    //         'answers.*.custom_value' => 'nullable|string|max:255',
    //         'answers.*.image_urls' => 'nullable|array', // Новый параметр для image_urls
    //         'answers.*.image_urls.*' => 'nullable|image', // Проверка, что это изображения
    //     ]);

    //     if ($validatedData->fails()) {
    //         return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
    //     }

    //     $validatedData = $validatedData->validated();

    //     // Обработка каждого ответа
    //     foreach ($validatedData['answers'] as $answer) {
    //         // Находим ответ для данного видео, анкеты и вопроса
    //         $defaultAnswer = UserAnswer::where('survey_id', $validatedData['survey_id'])
    //             ->where('video_id', $video_id)
    //             ->where('question_id', $answer['question_id'])
    //             ->where('user_id', auth()->id()) // Используем user_id вместо master_id
    //             ->first();

    //         // Если ответ найден, обновляем его
    //         if ($defaultAnswer) {
    //             // Применяем логику валидации в зависимости от типа вопроса
    //             $question = Question::find($answer['question_id']);
    //             if ($question) {
    //                 // Логика для вопросов типа radio
    //                 if ($question->type == 'radio' && isset($answer['option_ids']) && count($answer['option_ids']) != 1) {
    //                     return response()->json(['error' => 'Для вопроса типа radio должен быть выбран только один вариант'], Response::HTTP_BAD_REQUEST);
    //                 }
    //                 // Логика для вопросов типа checkbox
    //                 if ($question->type == 'checkbox' && isset($answer['option_ids']) && count($answer['option_ids']) < 1) {
    //                     return response()->json(['error' => 'Для вопроса типа checkbox нужно выбрать хотя бы один вариант'], Response::HTTP_BAD_REQUEST);
    //                 }
    //             }

    //             // Обработка изображений (если они есть)
    //             if (isset($answer['image_urls'])) {
    //                 $compressedImagePaths = [];

    //                 foreach ($answer['image_urls'] as $image) {
    //                     // Сохраняем оригинальное изображение
    //                     $originalPath = $image->store('photos/default_answers', 'public');
    //                     $originalFullPath = storage_path('app/public/' . $originalPath);

    //                     // Сжимаем изображение
    //                     $compressedPath = 'photos/default_answers/compressed/' . pathinfo($originalPath, PATHINFO_FILENAME) . '-compressed.' . $image->getClientOriginalExtension();
    //                     $compressedFullPath = storage_path('app/public/' . $compressedPath);

    //                     // Создаем новый объект ImageManager и сжимаем изображение
    //                     $manager = new ImageManager(Driver::class);
    //                     $imageObj = $manager->read($originalFullPath);
    //                     $imageObj->save($compressedFullPath, 50); // Сжатие до 50%

    //                     // Удаляем оригинальное изображение
    //                     if (File::exists($originalFullPath)) {
    //                         File::delete($originalFullPath);
    //                     }

    //                     // Добавляем путь к сжимаемому изображению в массив
    //                     $compressedImagePaths[] = $compressedPath;
    //                 }

    //                 // Преобразуем массив сжимаемых изображений в строку JSON
    //                 $answer['image_urls'] = json_encode($compressedImagePaths);
    //             }

    //             // Обновляем ответ в базе данных
    //             $defaultAnswer->option_ids = isset($answer['option_ids']) ? json_encode($answer['option_ids']) : null;
    //             $defaultAnswer->custom_value = $answer['custom_value'] ?? null;
    //             $defaultAnswer->image_urls = $answer['image_urls'] ?? null; // Сохраняем сжатые изображения
    //             $defaultAnswer->save();
    //         } else {
    //             // Если ответ не найден, возвращаем ошибку
    //             return response()->json(['error' => 'Ответ не найден'], Response::HTTP_NOT_FOUND);
    //         }
    //     }

    //     return response()->json(['message' => 'Answers updated successfully'], Response::HTTP_OK);
    // }




    /**
     * @OA\Delete(
     *     path="/api/default_answers/{id}",
     *     tags={"DefaultAnswers"},
     *     security={{"sanctum": {}}},
     *     summary="Delete a default answer",
     *     description="Deletes a default answer by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the default answer to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Default answer deleted successfully"),
     *     @OA\Response(response=404, description="Default answer not found")
     * )
     */
    public function destroy($id)
    {
        $defaultAnswer = DefaultAnswer::find($id);

        if (!$defaultAnswer) {
            return response()->json(['message' => 'Default answer not found'], Response::HTTP_NOT_FOUND);
        }

        $defaultAnswer->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
