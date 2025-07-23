<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Option;
use Illuminate\Support\Facades\Validator;

class OptionsController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/options",
     *     tags={"Options"},
     *     security={{"sanctum": {}}},
     *     summary="Options",
     *     description="Options",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        // $options = Option::where('question_id', $question_id)->get();
        $options = Option::all();
        return response()->json($options, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/options",
     *     tags={"Options"},
     *     security={{"sanctum": {}}},
     *     summary="Create a new option",
     *     description="Adds a new option to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"question_id", "option_text"},
     *                 @OA\Property(
     *                     property="question_id",
     *                     type="integer",
     *                     description="ID вопроса, к которому относится этот вариант",
     *                     example="1"
     *                 ),
     *                 @OA\Property(
     *                     property="option_text",
     *                     type="string",
     *                     description="Текст варианта ответа",
     *                     example="Sample Option Text"
     *                 ),
     *                 @OA\Property(
     *                     property="comment",
     *                     type="string",
     *                     description="Коммент",
     *                     example="Sample Option Text"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Option created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {

        $validatedData = Validator::make($request->all(), [
            'question_id' => 'required|exists:questions,id',
            'option_text' => 'required|string|max:255',
            'comment' => 'nullable|string',
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $option = Option::create($validatedData->validated());

        return response()->json($option, Response::HTTP_OK);
    }
    /**
     * @OA\Get(
     *     path="/api/options/{id}",
     *     tags={"Options"},
     *     security={{"sanctum": {}}},
     *     summary="Get an option by ID",
     *     description="Returns a single option",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the option",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Option not found")
     * )
     */
    public function show($id)
    {
        $option = Option::find($id);

        if (!$option) {
            return response()->json(['message' => 'Option not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($option, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/options/{id}",
     *     tags={"Options"},
     *     security={{"sanctum": {}}},
     *     summary="Update an option",
     *     description="Updates an existing option",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the option to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="question_id", type="integer", description="ID of the question the option belongs to"),
     *             @OA\Property(property="option_text", type="string", description="Text of the option"),
     *             @OA\Property(property="comment", type="string", description="")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Option updated successfully"),
     *     @OA\Response(response=404, description="Option not found"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function update(Request $request, $id)
    {
        $option = Option::find($id);

        if (!$option) {
            return response()->json(['message' => 'Option not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'question_id' => 'sometimes|exists:questions,id',
            'option_text' => 'sometimes|string|max:255',
            'comment' => 'sometimes|string',
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $validatedData = $validatedData->validated();

        if ($request->filled('question_id')) {
            $option->question_id = $validatedData['question_id'];
        }

        if ($request->filled('option_text')) {
            $option->option_text = $validatedData['option_text'];
        }

        if ($request->filled('comment')) {
            $option->comment = $validatedData['comment'];
        }

        $option->save();

        return response()->json($option, Response::HTTP_OK);
    }


    /**
     * @OA\Delete(
     *     path="/api/options/{id}",
     *     tags={"Options"},
     *     security={{"sanctum": {}}},
     *     summary="Delete an option",
     *     description="Deletes an option by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the option to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Option deleted successfully"),
     *     @OA\Response(response=404, description="Option not found")
     * )
     */
    public function destroy($id)
    {
        $option = Option::find($id);

        if (!$option) {
            return response()->json(['message' => 'Option not found'], Response::HTTP_NOT_FOUND);
        }

        $option->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
