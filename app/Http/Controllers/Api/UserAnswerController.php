<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAnswer; // Ensure you have a UserAnswer model
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserAnswerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user-answers",
     *     tags={"User Answers"},
     *     summary="Get a list of user answers",
     *     description="Returns a list of all user answers.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $userAnswers = UserAnswer::all();
        return response()->json($userAnswers, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/user-answers",
     *     tags={"User Answers"},
     *     summary="Create a new user answer",
     *     description="Creates a new user answer",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "question_id", "answer"},
     *             @OA\Property(property="user_id", type="integer", description="ID of the user"),
     *             @OA\Property(property="question_id", type="integer", description="ID of the question this answer is associated with"),
     *             @OA\Property(property="answer", type="string", description="Text of the user answer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User answer created successfully"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'question_id' => 'required|integer|exists:questions,id',
            'answer' => 'required|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $userAnswer = UserAnswer::create($validatedData->validated());

        return response()->json($userAnswer, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/user-answers/{id}",
     *     tags={"User Answers"},
     *     summary="Get a user answer by ID",
     *     description="Returns a single user answer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user answer",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="User answer not found")
     * )
     */
    public function show($id)
    {
        $userAnswer = UserAnswer::find($id);

        if (!$userAnswer) {
            return response()->json(['message' => 'User answer not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($userAnswer, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/user-answers/{id}",
     *     tags={"User Answers"},
     *     summary="Update a user answer",
     *     description="Updates an existing user answer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user answer to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", description="ID of the user"),
     *             @OA\Property(property="question_id", type="integer", description="ID of the question this answer is associated with"),
     *             @OA\Property(property="answer", type="string", description="Text of the user answer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User answer updated successfully"),
     *     @OA\Response(response=404, description="User answer not found"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function update(Request $request, $id)
    {
        $userAnswer = UserAnswer::find($id);

        if (!$userAnswer) {
            return response()->json(['message' => 'User answer not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'question_id' => 'sometimes|required|integer|exists:questions,id',
            'answer' => 'sometimes|required|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $userAnswer->update($validatedData->validated());

        return response()->json($userAnswer, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/user-answers/{id}",
     *     tags={"User Answers"},
     *     summary="Delete a user answer",
     *     description="Deletes a user answer by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user answer to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="User answer deleted successfully"),
     *     @OA\Response(response=404, description="User answer not found")
     * )
     */
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
