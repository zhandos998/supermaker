<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Variable; // Make sure to create the Variable model
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class VariableController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/variables",
     *     tags={"Variables"},
     *     summary="Get a list of variables",
     *     description="Returns a list of all variables.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $variables = Variable::all();
        return response()->json($variables, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/variables/{id}",
     *     tags={"Variables"},
     *     summary="Get a variable by ID",
     *     description="Returns a single variable",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the variable to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Variable found"
     *     ),
     *     @OA\Response(response=404, description="Variable not found")
     * )
     */
    public function show($id)
    {
        $variable = Variable::find($id);

        if (!$variable) {
            return response()->json(['message' => 'Variable not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($variable, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/variables",
     *     tags={"Variables"},
     *     summary="Create a new variable",
     *     description="Adds a new variable to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "value"},
     *             @OA\Property(property="name", type="string", example="Sample Variable"),
     *             @OA\Property(property="value", type="string", example="Sample Value")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Variable created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:variables,name',
            'value' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $variable = Variable::create($validatedData->validated());
        return response()->json($variable, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/variables/{id}",
     *     tags={"Variables"},
     *     summary="Update an existing variable",
     *     description="Updates variable details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the variable to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Variable"),
     *             @OA\Property(property="value", type="string", example="Updated Value")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Variable updated"
     *     ),
     *     @OA\Response(response=404, description="Variable not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $variable = Variable::find($id);

        if (!$variable) {
            return response()->json(['message' => 'Variable not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255|unique:variables,name,' . $id,
            'value' => 'nullable|string',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $variable->update($validatedData->validated());
        return response()->json($variable, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/variables/{id}",
     *     tags={"Variables"},
     *     summary="Delete a variable",
     *     description="Deletes a variable by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the variable to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Variable deleted"),
     *     @OA\Response(response=404, description="Variable not found")
     * )
     */
    public function destroy($id)
    {
        $variable = Variable::find($id);

        if (!$variable) {
            return response()->json(['message' => 'Variable not found'], Response::HTTP_NOT_FOUND);
        }

        $variable->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
