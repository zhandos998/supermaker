<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question; // Ensure you have a Question model
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/questions",
     *     tags={"Questions"},
     *     security={{"sanctum": {}}},
     *     summary="Get a list of questions",
     *     description="Returns a list of all questions.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $questions = Question::all();
        return response()->json($questions, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/questions",
     *     tags={"Questions"},
     *     security={{"sanctum": {}}},
     *     summary="Create a new question",
     *     description="Creates a new question",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"survey_id", "text"},
     *             @OA\Property(property="survey_id", type="integer", description="ID of the survey the question belongs to"),
     *             @OA\Property(property="text", type="string", description="Text of the question"),
     *             @OA\Property(property="type_id", type="integer", description="Type of the question (e.g., text, multiple choice)"),
     *             @OA\Property(property="comment", type="string", description="")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Question created successfully"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'survey_id' => 'required|exists:surveys,id',
            // 'title' => 'required|string|max:255',
            'text' => 'required|string|max:255',
            'type_id' => 'required|integer',
            'comment' => 'nullable|string'
            // 'options' => 'nullable|array',
            // 'options.*' => 'string|max:255', // Ensure each option is a string
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $question = Question::create($validatedData->validated());

        return response()->json($question, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/questions/{id}",
     *     tags={"Questions"},
     *     security={{"sanctum": {}}},
     *     summary="Get a question by ID",
     *     description="Returns a single question",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the question",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Question not found")
     * )
     */
    public function show($id)
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($question, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/questions/{id}",
     *     tags={"Questions"},
     *     security={{"sanctum": {}}},
     *     summary="Update a question",
     *     description="Updates an existing question",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the question to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="survey_id", type="integer", description="ID of the survey the question belongs to"),
     *             @OA\Property(property="text", type="string", description="Text of the question"),
     *             @OA\Property(property="type_id", type="integer", description="Type of the question (e.g., text, multiple choice)"),
     *             @OA\Property(property="comment", type="string", description="")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Question updated successfully"),
     *     @OA\Response(response=404, description="Question not found"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function update(Request $request, $id)
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'survey_id' => 'sometimes|exists:surveys,id',
            'text' => 'sometimes|string|max:255',
            // 'title' => 'sometimes|string|max:255',
            'type_id' => 'sometimes|integer',
            'comment' => 'nullable|string'
            // 'options' => 'sometimes|nullable|array',
            // 'options.*' => 'string|max:255',
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $validatedData = $validatedData->validated();

        if($request->filled('survey_id')){
            $question->survey_id = $validatedData['survey_id'];
        }

        if($request->filled('text')){
            $question->text = $validatedData['text'];
        }

        if($request->filled('type_id')){
            $question->type_id = $validatedData['type_id'];
        }

        if($request->filled('comment')){
            $question->comment = $validatedData['comment'];
        }

        $question->save();

        return response()->json($question, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/questions/{id}",
     *     tags={"Questions"},
     *     security={{"sanctum": {}}},
     *     summary="Delete a question",
     *     description="Deletes a question by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the question to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Question deleted successfully"),
     *     @OA\Response(response=404, description="Question not found")
     * )
     */
    public function destroy($id)
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }

        $question->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
