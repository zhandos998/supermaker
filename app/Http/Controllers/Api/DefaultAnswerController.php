<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DefaultAnswer; // Ensure you have a DefaultAnswer model
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class DefaultAnswerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/default-answers",
     *     tags={"Default Answers"},
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
        $defaultAnswers = DefaultAnswer::all();
        return response()->json($defaultAnswers, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/default-answers",
     *     tags={"Default Answers"},
     *     summary="Create a new default answer",
     *     description="Creates a new default answer",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question_id", "answer"},
     *             @OA\Property(property="question_id", type="integer", description="ID of the question this answer is associated with"),
     *             @OA\Property(property="answer", type="string", description="Text of the default answer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Default answer created successfully"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'question_id' => 'required|integer|exists:questions,id',
            'answer' => 'required|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $defaultAnswer = DefaultAnswer::create($validatedData->validated());

        return response()->json($defaultAnswer, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/default-answers/{id}",
     *     tags={"Default Answers"},
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
        $defaultAnswer = DefaultAnswer::find($id);

        if (!$defaultAnswer) {
            return response()->json(['message' => 'Default answer not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($defaultAnswer, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/default-answers/{id}",
     *     tags={"Default Answers"},
     *     summary="Update a default answer",
     *     description="Updates an existing default answer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the default answer to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="question_id", type="integer", description="ID of the question this answer is associated with"),
     *             @OA\Property(property="answer", type="string", description="Text of the default answer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Default answer updated successfully"),
     *     @OA\Response(response=404, description="Default answer not found"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function update(Request $request, $id)
    {
        $defaultAnswer = DefaultAnswer::find($id);

        if (!$defaultAnswer) {
            return response()->json(['message' => 'Default answer not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'question_id' => 'sometimes|required|integer|exists:questions,id',
            'answer' => 'sometimes|required|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $defaultAnswer->update($validatedData->validated());

        return response()->json($defaultAnswer, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/default-answers/{id}",
     *     tags={"Default Answers"},
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
