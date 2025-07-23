<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestionType; // Ensure you have a Question model
// use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class QuestionTypesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/question_types",
     *     tags={"Question Types"},
     *     security={{"sanctum": {}}},
     *     summary="Get a list of question types",
     *     description="Returns a list of all question types.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $questionTypes = QuestionType::all();
        return response()->json($questionTypes, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/question_types",
     *     tags={"Question Types"},
     *     security={{"sanctum": {}}},
     *     summary="Create a new question type",
     *     description="Creates a new question type",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="Name of the question type")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Question type created successfully"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $questionType = QuestionType::create($validatedData->validated());

        return response()->json($questionType, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/question_types/{id}",
     *     tags={"Question Types"},
     *     security={{"sanctum": {}}},
     *     summary="Get a question type by ID",
     *     description="Returns a single question type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the question type",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Question type not found")
     * )
     */
    public function show($id)
    {
        $questionType = QuestionType::find($id);

        if (!$questionType) {
            return response()->json(['message' => 'Question type not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($questionType, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/question_types/{id}",
     *     tags={"Question Types"},
     *     security={{"sanctum": {}}},
     *     summary="Update a question type",
     *     description="Updates an existing question type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the question type to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Name of the question type")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Question type updated successfully"),
     *     @OA\Response(response=404, description="Question type not found"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function update(Request $request, $id)
    {
        $questionType = QuestionType::find($id);

        if (!$questionType) {
            return response()->json(['message' => 'Question type not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $questionType->update($validatedData->validated());

        return response()->json($questionType, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/question_types/{id}",
     *     tags={"Question Types"},
     *     security={{"sanctum": {}}},
     *     summary="Delete a question type",
     *     description="Deletes a question type by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the question type to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Question type deleted successfully"),
     *     @OA\Response(response=404, description="Question type not found")
     * )
     */
    public function destroy($id)
    {
        $questionType = QuestionType::find($id);

        if (!$questionType) {
            return response()->json(['message' => 'Question type not found'], Response::HTTP_NOT_FOUND);
        }

        $questionType->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
