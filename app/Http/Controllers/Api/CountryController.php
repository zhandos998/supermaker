<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/countries",
     *     tags={"Countries"},
     *     summary="Get list of countries",
     *     description="Returns a list of all countries.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $countries = Country::all();
        return response()->json($countries, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/countries/{id}",
     *     tags={"Countries"},
     *     summary="Get a country by ID",
     *     description="Returns a single country",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of country to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Country found"
     *     ),
     *     @OA\Response(response=404, description="Country not found")
     * )
     */
    public function show($id)
    {
        $country = Country::find($id);

        if (!$country) {
            return response()->json(['message' => 'Country not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($country, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/countries",
     *     tags={"Countries"},
     *     summary="Create a new country",
     *     description="Adds a new country to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "code"},
     *             @OA\Property(property="name", type="string", example="Kazakhstan"),
     *             @OA\Property(property="code", type="string", example="KZ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Country created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:3|unique:countries',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $country = Country::create($validatedData->validated());
        return response()->json($country, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/countries/{id}",
     *     tags={"Countries"},
     *     summary="Update an existing country",
     *     description="Updates country details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of country to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Kazakhstan"),
     *             @OA\Property(property="code", type="string", example="KZ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Country updated"
     *     ),
     *     @OA\Response(response=404, description="Country not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $country = Country::find($id);

        if (!$country) {
            return response()->json(['message' => 'Country not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'code' => 'string|max:3|unique:countries,code,' . $id,
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $country->update($validatedData->validated());
        return response()->json($country, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/countries/{id}",
     *     tags={"Countries"},
     *     summary="Delete a country",
     *     description="Deletes a country by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of country to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Country deleted"),
     *     @OA\Response(response=404, description="Country not found")
     * )
     */
    public function destroy($id)
    {
        $country = Country::find($id);

        if (!$country) {
            return response()->json(['message' => 'Country not found'], Response::HTTP_NOT_FOUND);
        }

        $country->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
