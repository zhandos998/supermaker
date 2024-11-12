<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating; // Ensure you have a Rating model
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/ratings",
     *     tags={"Ratings"},
     *     summary="Get a list of ratings",
     *     description="Returns a list of all ratings.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $ratings = Rating::all();
        return response()->json($ratings, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/ratings/{id}",
     *     tags={"Ratings"},
     *     summary="Get a rating by ID",
     *     description="Returns a single rating",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the rating to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rating found"
     *     ),
     *     @OA\Response(response=404, description="Rating not found")
     * )
     */
    public function show($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($rating, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/ratings",
     *     tags={"Ratings"},
     *     summary="Create a new rating",
     *     description="Adds a new rating to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"value", "user_id", "product_id"},
     *             @OA\Property(property="value", type="integer", example=4, description="Rating value"),
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID of the user"),
     *             @OA\Property(property="product_id", type="integer", example=1, description="ID of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rating created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'value' => 'required|integer|between:1,5', // Assuming ratings are between 1 and 5
            'user_id' => 'required|integer|exists:users,id',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $rating = Rating::create($validatedData->validated());
        return response()->json($rating, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/ratings/{id}",
     *     tags={"Ratings"},
     *     summary="Update an existing rating",
     *     description="Updates rating details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the rating to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="value", type="integer", example=5, description="Updated rating value"),
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID of the user"),
     *             @OA\Property(property="product_id", type="integer", example=1, description="ID of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rating updated"
     *     ),
     *     @OA\Response(response=404, description="Rating not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'value' => 'nullable|integer|between:1,5',
            'user_id' => 'nullable|integer|exists:users,id',
            'product_id' => 'nullable|integer|exists:products,id',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $rating->update(array_filter($validatedData->validated())); // Update only provided fields
        return response()->json($rating, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/ratings/{id}",
     *     tags={"Ratings"},
     *     summary="Delete a rating",
     *     description="Deletes a rating by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the rating to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Rating deleted"),
     *     @OA\Response(response=404, description="Rating not found")
     * )
     */
    public function destroy($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], Response::HTTP_NOT_FOUND);
        }

        $rating->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
