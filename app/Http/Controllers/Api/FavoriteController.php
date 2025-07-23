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
     *     security={{"sanctum": {}}},
     *     summary="Get a list of favorites",
     *     description="Returns a list of all favorite items for the authenticated user.",
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
    public function index()
    {
        // Получаем авторизованного пользователя через Sanctum
         $user = auth()->user();

        // // Если пользователь не авторизован, возвращаем ошибку
        // if (!$user) {
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        // Получаем список избранных для текущего пользователя
        // $favorites = Favorite::where('user_id', $user->id)->get();
        $favorites = Favorite::with(['user', 'video'])->where('user_id', $user->id)->get();

        // Возвращаем результат
        return response()->json($favorites, Response::HTTP_OK);

    }


    // public function index(Request $request)
    // {
    //     // return $request->user()->id;
    //     // dd($request->user()->id);
    //     $favorites = Favorite::where('user_id', Auth::user()->id)->get();
    //     return response()->json($favorites, Response::HTTP_OK);
    // }

    /**
     * @OA\Post(
     *     path="/api/favorites",
     *     tags={"Favorites"},
     *     security={{"sanctum": {}}},
     *     summary="Add a favorite item",
     *     description="Adds a new item to the user's favorites list",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"video_id"},
     *             @OA\Property(property="video_id", type="integer", description="ID of the item to favorite")
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
            'video_id' => 'required|integer|exists:videos,id', // Ensure the item exists
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }


        $favorite = Favorite::firstOrCreate([
            'user_id' => auth()->id(),
            'video_id' => $request->video_id,
        ]);

        return response()->json($favorite, Response::HTTP_CREATED);
    }

    /**
     * @OA\Delete(
     *     path="/api/favorites/{id}",
     *     tags={"Favorites"},
     *     security={{"sanctum": {}}},
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
    public function destroy($video_id)
    {

        $favorite = Favorite::where('video_id', $video_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Favorite not found'], 404);
        }
        $favorite->delete();

//        $favorite = Favorite::where('id', $id)->where('user_id', Auth::id())->first();
//
//        if (!$favorite) {
//            return response()->json(['message' => 'Favorite item not found'], Response::HTTP_NOT_FOUND);
//        }
//
//        $favorite->delete();
        return response()->json(['message' => 'Favorite deleted successfully']);
    }
}
