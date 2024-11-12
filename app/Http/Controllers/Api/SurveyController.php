<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey; // Ensure you have a Survey model
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/surveys",
     *     tags={"Surveys"},
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
        $surveys = Survey::all();
        return response()->json($surveys, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/surveys",
     *     tags={"Surveys"},
     *     summary="Create a new survey",
     *     description="Creates a new survey",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description"},
     *             @OA\Property(property="title", type="string", description="Title of the survey"),
     *             @OA\Property(property="description", type="string", description="Description of the survey"),
     *             @OA\Property(property="questions", type="array", @OA\Items(type="string"), description="List of survey questions")
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
            'questions' => 'required|array',
            'questions.*' => 'string|max:255', // Ensure each question is a string
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $survey = Survey::create($validatedData->validated());

        return response()->json($survey, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/surveys/{id}",
     *     tags={"Surveys"},
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
        $survey = Survey::find($id);

        if (!$survey) {
            return response()->json(['message' => 'Survey not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($survey, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/surveys/{id}",
     *     tags={"Surveys"},
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
     *             @OA\Property(property="description", type="string", description="Description of the survey"),
     *             @OA\Property(property="questions", type="array", @OA\Items(type="string"), description="List of survey questions")
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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:1000',
            'questions' => 'sometimes|required|array',
            'questions.*' => 'string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $survey->update($validatedData->validated());

        return response()->json($survey, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/surveys/{id}",
     *     tags={"Surveys"},
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
