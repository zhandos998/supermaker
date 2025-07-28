<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Like;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Services\PushService;

class LikeController extends Controller
{
    // Получение всех подписок
    public function index()
    {
        // Получаем авторизованного пользователя через Sanctum
        $user = auth()->user();

        $likes = Like::with(['user', 'video'])->where('user_id', $user->id)->orderBy('id', 'desc')->get();
        return response()->json($likes, Response::HTTP_OK);
    }

    public function store(Request $request, PushService $pushService)
    {
        $validatedData = Validator::make($request->all(),[
            'video_id' => 'required|integer|exists:videos,id', // Ensure the item exists
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }


        $like = Like::firstOrCreate([
            'user_id' => auth()->id(),
            'video_id' => $request->video_id
        ]);

        $video = Video::with('user')->find($request->video_id);

        if ($video && $video->user && $video->user->device_token) {
            // Отправляем пуш владельцу видео
            $pushService->sendToDevice(
                $video->user->device_token, // токен устройства владельца
                'Новый лайк ❤',
                auth()->user()->name . ' поставил лайк на ваше видео!'
            );
        }

        return response()->json($like, Response::HTTP_CREATED);
    }

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
