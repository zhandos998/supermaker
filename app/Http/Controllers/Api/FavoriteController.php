<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite; // Ensure you have a Favorite model
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/favorites",
     *     tags={"Favorites"},
     *     summary="Get a list of favorites",
     *     description="Returns a list of all favorite items for the authenticated user.",
     *     security={"bearerAuth": {}},
     *     operationId="",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
    *     @OA\Response(
    *         response=401,
    *         description="Unauthorized"
    *     )
     * )
     */
    public function index(Request $request)
    {
        // dd($request->user()->id);
        $favorites = Favorite::where('user_id', $request->user()->id)->get();
        return response()->json($favorites, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/favorites",
     *     tags={"Favorites"},
     *     summary="Add a favorite item",
     *     description="Adds a new item to the user's favorites list",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"item_id"},
     *             @OA\Property(property="item_id", type="integer", description="ID of the item to favorite")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Favorite item created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:items,id', // Ensure the item exists
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $favorite = Favorite::create([
            'user_id' => $request->user()->id,
            'item_id' => $validatedData->validated()['item_id'],
        ]);

        return response()->json($favorite, Response::HTTP_CREATED);
    }

    /**
     * @OA\Delete(
     *     path="/api/favorites/{id}",
     *     tags={"Favorites"},
     *     summary="Remove a favorite item",
     *     description="Removes an item from the user's favorites list",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the favorite item to remove",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Favorite item deleted"),
     *     @OA\Response(response=404, description="Favorite item not found")
     * )
     */
    public function destroy($id)
    {
        $favorite = Favorite::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$favorite) {
            return response()->json(['message' => 'Favorite item not found'], Response::HTTP_NOT_FOUND);
        }

        $favorite->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
