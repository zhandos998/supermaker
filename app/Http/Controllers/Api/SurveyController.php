<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DefaultAnswer;
use App\Models\DefaultSurvey;
use App\Models\Survey; // Ensure you have a Survey model
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/surveys/GetSurveyWithDefaultAnswers/{video_id}",
     *     summary="Получить вопросы с опциями и значениями по ID видео и анкеты",
     *     description="Возвращает вопросы анкеты с указанным survey_id, включая связанные опции и значения custom_value, основываясь на DefaultAnswer для указанного video_id.",
     *     tags={"Surveys"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="video_id",
     *         in="path",
     *         required=true,
     *         description="ID видео для фильтрации DefaultAnswer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 description="ID анкеты"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 description="Название анкеты"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Описание анкеты"
     *             ),
     *             @OA\Property(
     *                 property="questions",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", description="ID вопроса"),
     *                     @OA\Property(property="text", type="string", description="Текст вопроса"),
     *                     @OA\Property(property="type_id", type="integer", description="Тип вопроса"),
     *                     @OA\Property(
     *                         property="options",
     *                         type="array",
     *                         description="Список связанных опций",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", description="ID опции"),
     *                             @OA\Property(property="option_text", type="string", description="Текст опции"),
     *                             @OA\Property(property="answered", type="boolean", description="Помечена ли опция как выбранная")
     *                         )
     *                     ),
     *                     @OA\Property(property="custom_value", type="string", description="Пользовательское значение для вопроса", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Анкета или видео не найдены",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Survey not found")
     *         )
     *     )
     * )
     */
    public function GetSurveyWithDefaultAnswers($video_id, $survey_id = 1)
    {
        // Загружаем survey с указанным ID
        $survey = Survey::with(['questions.options'])->find($survey_id);

        if (!$survey) {
            return response()->json(['message' => 'Survey not found'], Response::HTTP_NOT_FOUND);
        }

        $video_user_id = Video::find($video_id)->user_id;

        if (!$video_user_id) {
            return response()->json(['message' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }
        // Загружаем DefaultAnswers для указанного survey_id и master_id
        $defaultAnswer = DefaultSurvey::where('survey_id', $survey_id)
            ->where('master_id', $video_user_id)
            ->first();
        $defaultAnswers = DefaultAnswer::where('default_survey_id', $defaultAnswer->id)
            ->get();

        // Обрабатываем questions, добавляя опции и custom_value
        $survey->questions->each(function ($question) use ($defaultAnswers) {
            // Ищем ответ для текущего вопроса
            $relatedAnswer = $defaultAnswers->firstWhere('question_id', $question->id);

            if ($relatedAnswer) {
                // Получаем IDs выбранных опций
                $optionIds = json_decode($relatedAnswer->option_ids, true);

                // Обновляем опции, помечая выбранные
                $question->options->each(function ($option) use ($optionIds) {
                    $option->answered = in_array($option->id, $optionIds ?? []);
                });

                // Добавляем custom_value
                $question->custom_value = $relatedAnswer->custom_value;
            } else {
                // Если ответа нет, очищаем опции и custom_value
                $question->options->each(function ($option) {
                    $option->answered = false;
                });
                $question->custom_value = null;
            }
        });

        // Форматируем результат в нужный JSON-формат
        return response()->json($survey, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/surveys",
     *     tags={"Surveys"},
     *     security={{"sanctum": {}}},
     *     summary="Get a list of surveys",
     *     description="Returns a list of all surveys.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        // $surveys = Survey::all();

        $surveys = Survey::with('questions.options')->get();

        return response()->json($surveys, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/surveys",
     *     tags={"Surveys"},
     *     security={{"sanctum": {}}},
     *     summary="Create a new survey",
     *     description="Creates a new survey",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description"},
     *             @OA\Property(property="title", type="string", description="Title of the survey"),
     *             @OA\Property(property="description", type="string", description="Description of the survey")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Survey created successfully"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $survey = Survey::create($validatedData->validated());

        return response()->json($survey, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/surveys/{id}",
     *     tags={"Surveys"},
     *     security={{"sanctum": {}}},
     *     summary="Get a survey by ID",
     *     description="Returns a single survey",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the survey",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Survey not found")
     * )
     */
    public function show($id)
    {
//        $survey = Survey::find($id)->with('questions.options')->get();
        $survey = Survey::with('questions.options')->find($id);
        // $surveys = Survey::with('questions.options')->get();
        if (!$survey) {
            return response()->json(['message' => 'Survey not found'], Response::HTTP_NOT_FOUND);
        }
        $data = [
            'id' => $survey->id,
            'title' => $survey->title,
            'description' => $survey->description,
            'questions' => $survey->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'text' => $q->text,
                    'type_id' => $q->type_id,
                    'options' => $q->options->map(function ($o) {
                        return [
                            'id' => $o->id,
                            'option_text' => $o->option_text,
                        ];
                    }),
                ];
            }),
        ];

//        return response()->json($data);
        return response()->json($data, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/surveys/{id}",
     *     tags={"Surveys"},
     *     security={{"sanctum": {}}},
     *     summary="Update a survey",
     *     description="Updates an existing survey",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the survey to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", description="Title of the survey"),
     *             @OA\Property(property="description", type="string", description="Description of the survey")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Survey updated successfully"),
     *     @OA\Response(response=404, description="Survey not found"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function update(Request $request, $id)
    {
        $survey = Survey::find($id);

        if (!$survey) {
            return response()->json(['message' => 'Survey not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }
        $validatedData = $validatedData->validated();

        if($request->filled('title')){
            $survey->title = $validatedData['title'];
        }
        if($request->filled('description')){
            $survey->description = $validatedData['description'];
        }
        $survey->update();

        return response()->json($survey, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/surveys/{id}",
     *     tags={"Surveys"},
     *     security={{"sanctum": {}}},
     *     summary="Delete a survey",
     *     description="Deletes a survey by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the survey to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Survey deleted successfully"),
     *     @OA\Response(response=404, description="Survey not found")
     * )
     */
    public function destroy($id)
    {
        $survey = Survey::find($id);

        if (!$survey) {
            return response()->json(['message' => 'Survey not found'], Response::HTTP_NOT_FOUND);
        }

        $survey->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
