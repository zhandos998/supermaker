<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cities",
     *     tags={"Cities"},
     *     summary="Get list of cities",
     *     description="Returns a list of all cities.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $cities = City::all();
        return response()->json($cities, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/cities/{id}",
     *     tags={"Cities"},
     *     summary="Get a city by ID",
     *     description="Returns a single city",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of city to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="City found"
     *     ),
     *     @OA\Response(response=404, description="City not found")
     * )
     */
    public function show($id)
    {
        $city = City::find($id);

        if (!$city) {
            return response()->json(['message' => 'City not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($city, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/cities",
     *     tags={"Cities"},
     *     summary="Create a new city",
     *     description="Adds a new city to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "country_id"},
     *             @OA\Property(property="name", type="string", example="Almaty"),
     *             @OA\Property(property="country_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="City created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'country_id' => 'required|integer|exists:countries,id',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $city = City::create($validatedData->validated());
        return response()->json($city, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/cities/{id}",
     *     tags={"Cities"},
     *     summary="Update an existing city",
     *     description="Updates city details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of city to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Astana"),
     *             @OA\Property(property="country_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="City updated"
     *     ),
     *     @OA\Response(response=404, description="City not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $city = City::find($id);

        if (!$city) {
            return response()->json(['message' => 'City not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'country_id' => 'integer|exists:countries,id',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $city->update($validatedData->validated());
        return response()->json($city, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/cities/{id}",
     *     tags={"Cities"},
     *     summary="Delete a city",
     *     description="Deletes a city by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of city to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="City deleted"),
     *     @OA\Response(response=404, description="City not found")
     * )
     */
    public function destroy($id)
    {
        $city = City::find($id);

        if (!$city) {
            return response()->json(['message' => 'City not found'], Response::HTTP_NOT_FOUND);
        }

        $city->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
