<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Like;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/likes",
     *     tags={"Likes"},
     *     security={{"sanctum": {}}},
     *     summary="Get a list of likes",
     *     description="Returns a list of all like items for the authenticated user.",
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
    // Получение всех подписок
    public function index()
    {
        // Получаем авторизованного пользователя через Sanctum
        $user = auth()->user();

        $likes = Like::with(['user', 'video'])->where('user_id', $user->id)->get();
        return response()->json($likes, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/likes",
     *     tags={"Likes"},
     *     security={{"sanctum": {}}},
     *     summary="Add a like item",
     *     description="Adds a new item to the user's likes list",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"video_id"},
     *             @OA\Property(property="video_id", type="integer", description="ID of the video to like")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="like item created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(),[
            'video_id' => 'required|integer|exists:videos,id', // Ensure the item exists
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $like = Like::firstOrCreate([
            'user_id' => auth()->id(),
            'video_id' => $request->video_id
        ]);
        return response()->json($like, Response::HTTP_CREATED);
    }

    /**
     * @OA\Delete(
     *     path="/api/likes/{id}",
     *     tags={"Likes"},
     *     security={{"sanctum": {}}},
     *     summary="Remove a like item",
     *     description="Removes an item from the user's likes list",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the like item to remove",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="like item deleted"),
     *     @OA\Response(response=404, description="like item not found")
     * )
     */
    public function destroy($video_id)
    {
//        $like = Like::findOrFail($id);
        $like = Like::where('video_id', $video_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$like) {
            return response()->json(['message' => 'Like not found'], 404);
        }
        
        $like->delete();

        return response()->json(['message' => 'Like deleted successfully']);
    }
}
