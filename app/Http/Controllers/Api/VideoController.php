<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SharedLink;
use App\Models\User;
use App\Models\Variable;
use App\Models\Video;
use App\Models\VideoTag;
use App\Models\VideoView;
use App\Models\VideoClick;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\FrameRate;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;


use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\EncodedImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/videos/{id}/tapped",
     *     tags={"Videos"},
     *     security={{"sanctum": {}}},
     *     summary="Increment video tapped count",
     *     description="Increments the tapped count for a specified video. The user must be authenticated.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the video whose tapped count is to be incremented.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="views_count", type="integer", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Video not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong.")
     *         )
     *     )
     * )
     */
    public function incrementViewTappes(Request $request, $id)
    {
        VideoClick::create([
            'video_id' => $id,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);

        return response()->json(['message' => 'Video click recorded']);
//
//        $video = Video::find($id);
//
//        if (!$video) {
//            return response()->json(['message' => 'Video not found'], 404);
//        }
//
//        $video->tapped_count++;
//        $video->save();
//
//        return response()->json([
//            'id' => $video->id,
//            'tapped_count' => $video->tapped_count,
//        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/videos/{id}/views",
     *     tags={"Videos"},
     *     security={{"sanctum": {}}},
     *     summary="Increment video view count",
     *     description="Increments the view count for a specified video. The user must be authenticated.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the video whose view count is to be incremented.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="views_count", type="integer", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Video not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong.")
     *         )
     *     )
     * )
     */
    public function incrementViewCount(Request $request, $id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        VideoView::create([
            'video_id' => $video->id,
            'user_id' => auth()->id(), // Если пользователь авторизован
            'ip' => $request->ip(),
        ]);

        return response()->json(['message' => 'View recorded']);
//        return response()->json([
//            'id' => $video->id,
//            'tapped_count' => $video->tapped_count,
//        ], 200);
    }

//    public function incrementViewCount($id)
//    {
//        $video = Video::find($id);
//
//        if (!$video) {
//            return response()->json(['message' => 'Video not found'], 404);
//        }
//
//        $video->views_count++;
//        $video->save();
//
//        return response()->json([
//            'id' => $video->id,
//            'views_count' => $video->views_count,
//        ], 200);
//    }

    /**
     * @OA\Get(
     *     path="/api/share/{uniqueKey}",
     *     tags={"Videos"},
     *     summary="Handle shared link and return video data",
     *     description="Handles a shared link and returns the data of the associated video.",
     *     @OA\Parameter(
     *         name="uniqueKey",
     *         in="path",
     *         description="Unique key of the shared link.",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="1a2b3c4d5e6f"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Sample Video"),
     *             @OA\Property(property="url", type="string", example="videos/videos/sample.mp4"),
     *             @OA\Property(property="preview_url", type="string", example="photos/videos/sample_preview.png"),
     *             @OA\Property(property="description", type="string", example="This is a sample video."),
     *             @OA\Property(property="tags", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Tutorial")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Link not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong.")
     *         )
     *     )
     * )
     */
    public function generateShareLink($id, Request $request)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }

        // Генерация уникального ключа
        $uniqueKey = Str::uuid(); // Или используйте более читаемый ключ: Str::random(10)

        // Сохранение в базе данных
        $sharedLink = SharedLink::create([
            'user_id' => $request->user()->id ?? null, // ID текущего пользователя
            'video_id' => $video->id,
            'unique_key' => $uniqueKey,
        ]);

        // Создание URL
        $url = env('APP_URL') . "/share/{$uniqueKey}";

        return response()->json(['share_url' => $url], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/videos/GetVideosByTags",
     *     tags={"Videos"},
     *     security={{"sanctum": {}}},
     *     summary="Get videos by tags",
     *     description="Returns a list of videos associated with the specified tags.",
     *     @OA\Parameter(
     *         name="tags[]",
     *         in="query",
     *         description="List of tag names to filter videos by. Example: tags[]=Technology&tags[]=News",
     *         required=true,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="string", example="Technology")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=37),
     *                     @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="firstname", type="string", example="John"),
     *                     @OA\Property(property="lastname", type="string", example="Doe"),
     *                     @OA\Property(property="photo_url", type="string", example="photos/users/27_user.jpg"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-26T19:34:14.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-26T19:34:14.000000Z")
     *                 ),
     *                 @OA\Property(property="title", type="string", example="Sample Video"),
     *                 @OA\Property(property="furniture_type", type="string", example="Sample Video"),
     *                 @OA\Property(property="description", type="string", example="This is a sample video description."),
     *                 @OA\Property(property="url", type="string", example="videos/videos/sample.mp4"),
     *                 @OA\Property(property="price", type="string", example="10000.00"),
     *                 @OA\Property(property="sizes", type="string", example="1920x1080"),
     *                 @OA\Property(property="is_fixed", type="boolean", example=false),
     *                 @OA\Property(property="views_count", type="integer", example=100),
     *                 @OA\Property(property="preview_url", type="string", example="photos/videos/sample.png"),
     *                 @OA\Property(property="is_visible", type="boolean", example=true),
     *                 @OA\Property(property="tapped_count", type="integer", example=10),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-26T19:34:14.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-26T19:34:14.000000Z"),
     *                 @OA\Property(property="is_favorite", type="boolean", example=true),
     *                 @OA\Property(property="is_subscription", type="boolean", example=true),
     *                 @OA\Property(property="is_like", type="boolean", example=true),
     *                 @OA\Property(property="tags", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Tutorial")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No tags provided.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong.")
     *         )
     *     )
     * )
     */
    public function GetVideosByTags(Request $request)
    {
        // print_r($request->input());
        // return "";
        $user_id = auth()->id(); // Получение ID текущего пользователя

        // Получаем список тегов из запроса
        $tagNames = $request->input('tags', []); // Ожидается массив названий тегов


        if (empty($tagNames)) {
            return response()->json(['message' => 'No tags provided.'], Response::HTTP_BAD_REQUEST);
        }

        // Фильтруем видео, связанные с указанными тегами
        $videos = Video::with(['tags', 'user'])
            ->whereHas('tags', function ($query) use ($tagNames) {
                $query->whereIn('name', $tagNames);
            })
            ->get();

        // Добавляем дополнительные поля для каждого видео
        $videos->each(function ($video) use ($user_id) {
            $video->is_favorite = $video->favorites()
                ->where('user_id', $user_id)
                ->exists();
            $video->is_subscription = $video->user->subscriptions()
                ->where('master_id', $video->user_id)
                ->where('user_id', $user_id)
                ->exists();
            $video->is_like = $video->user->likes()
                ->where('master_id', $video->user_id)
                ->where('user_id', $user_id)
                ->exists();
        });

        return response()->json($videos, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/videos/GetVideosByUserID/{user_id}",
     *     tags={"Videos"},
     *     security={{"sanctum": {}}},
     *     summary="Get user videos",
     *     description="Returns a videos by user id.",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=2
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function GetVideosByUserID($user_id , Request $request)
    {
        $video_count = Video::where("user_id",$user_id)->count();
        $auth_id = auth()->id(); // Получение ID текущего пользователя


        if ($video_count == 0) {
            return response()->json(['message' => 'No videos available'], Response::HTTP_OK);
        }


        // Получаем видео с учетом смещения и лимита
        $videos = Video::with(['tags', 'user'])
            ->where("user_id",$user_id)
            ->get();

        // Добавляем поле `is_favorite` для каждого видео
        $videos->each(function ($video) use ($auth_id) {
            $video->is_favorite = $video->favorites()->where('user_id', $auth_id)->exists();
            $video->is_subscription = $video->user->subscriptions()->where('master_id', $video->user_id)->where('user_id', $auth_id)->exists();
            $video->is_like = $video->user->likes()->where('video_id', $video->id)->where('user_id', $auth_id)->exists();
        });

        return response()->json([
            'videos' => $videos
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/videos",
     *     tags={"Videos"},
     *     security={{"sanctum": {}}},
     *     summary="Get paginated videos list",
     *     description="Returns a paginated list of videos. If the end of the list is reached, it starts over from the beginning.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination (default is 1).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="current_page",
     *                 type="integer",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="videos",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=38),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=37),
     *                         @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                         @OA\Property(property="phone", type="string", example="77713985075"),
     *                         @OA\Property(property="username", type="string", example="johndoe"),
     *                         @OA\Property(property="city_id", type="string", example="1"),
     *                         @OA\Property(property="firstname", type="string", example="John"),
     *                         @OA\Property(property="lastname", type="string", example="Doe"),
     *                         @OA\Property(property="iin", type="string", example="123456789012"),
     *                         @OA\Property(property="is_visible", type="boolean", example=true),
     *                         @OA\Property(property="photo_url", type="string", example="photos/users/27_user.jpg"),
     *                         @OA\Property(property="is_verified", type="boolean", example=false),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-14T16:06:57.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-14T16:35:04.000000Z")
     *                     ),
     *                     @OA\Property(property="title", type="string", example="Sample Video"),
     *                     @OA\Property(property="furniture_type", type="string", example="Sample Video"),
     *                     @OA\Property(property="description", type="string", example="This is a sample video description."),
     *                     @OA\Property(property="url", type="string", example="videos/videos/iOzTxunfY5ebV7S7DJConO8NTemUuHHVkVVYiU3J.mp4"),
     *                     @OA\Property(property="price", type="string", example="10000.00"),
     *                     @OA\Property(property="sizes", type="string", example="10*10"),
     *                     @OA\Property(property="is_fixed", type="boolean", example=false),
     *                     @OA\Property(property="views_count", type="integer", example=0),
     *                     @OA\Property(property="preview_url", type="string", example="photos/videos/HQ0wfLKvasgWvoGVyNgOfuWEXXudUngs0SmkhzwE.png"),
     *                     @OA\Property(property="is_visible", type="boolean", example=true),
     *                     @OA\Property(property="tapped_count", type="integer", example=0),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-26T19:34:14.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-26T19:34:14.000000Z"),
     *                     @OA\Property(property="is_favorite", type="boolean", example=true),
     *                     @OA\Property(property="is_subscription", type="boolean", example=true),
     *                     @OA\Property(property="is_like", type="boolean", example=true),
     *                     @OA\Property(property="tags", type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Tutorial")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user_id = auth('sanctum')->id(); // Получение ID текущего пользователя

        // Получаем номер страницы (по умолчанию 1)
        $page = $request->input('page', 1);
        $limit = 2; // Количество видео на одной странице

        // Получаем общее количество видео
        $totalVideos = Video::count();

        if ($totalVideos == 0) {
            return response()->json(['message' => 'No videos available'], Response::HTTP_OK);
        }

        // Вычисляем смещение (начало выборки)
        $offset = ($page - 1) * $limit;

        // Если смещение превышает количество видео, начинаем с начала
        if ($offset >= $totalVideos) {
            $offset = $offset % $totalVideos;
        }

        // Получаем видео с учетом смещения и лимита
        $videos = Video::with(['tags', 'user'])
            ->orderBy('id','desc')
            ->skip($offset)
            ->take($limit)
            ->get();
//        /api/videos/133/views

        // Добавляем поле `is_like` для каждого видео
        $videos->each(function ($video) use ($user_id,$request) {
            $video->is_like = $video->likes()->where('user_id', $user_id)->exists();
            VideoView::create([
                'video_id' => $video->id,
                'user_id' => $user_id, // Если пользователь авторизован
                'ip' => $request->ip(),
            ]);
        });

        return response()->json([
            'current_page' => $page,
            'videos' => $videos
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/videos/{id}",
     *     tags={"Videos"},
     *     security={{"sanctum": {}}},
     *     summary="Get video details",
     *     description="Returns detailed information about a specific video, including its owner, tags, and favorite or subscription status.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the video to retrieve.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=38
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=38),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=37),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="phone", type="string", example="77777777777"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="firstname", type="string", example="John"),
     *                 @OA\Property(property="lastname", type="string", example="Doe")
     *             ),
     *             @OA\Property(property="title", type="string", example="Sample Video"),
     *             @OA\Property(property="furniture_type", type="string", example="Sample Video"),
     *             @OA\Property(property="description", type="string", example="This is a sample video description."),
     *             @OA\Property(property="url", type="string", example="videos/videos/sample_video.mp4"),
     *             @OA\Property(property="is_favorite", type="boolean", example=true),
     *             @OA\Property(property="is_subscription", type="boolean", example=true),
     *             @OA\Property(property="is_like", type="boolean", example=true),
     *             @OA\Property(property="tags", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Tutorial")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Video not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $user_id = auth('sanctum')->id(); // Получение ID текущего пользователя

        // Находим видео по ID
        $video = Video::with(['tags', 'user'])->find($id);

        if (!$video) {
            return response()->json(['message' => 'Video not found.'], Response::HTTP_NOT_FOUND);
        }

        // Добавляем поле `is_favorite` для видео
//        $video->is_favorite = $video->favorites()->where('user_id', $user_id)->exists();

        // Проверяем, подписан ли пользователь на владельца видео
//        $video->is_subscription = $video->user->subscriptions()->where('master_id', $video->user_id)->where('user_id', $user_id)->exists();
        $video->is_like = $video->likes()->where('user_id', $user_id)->exists();

        return response()->json($video, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/videos",
     *     tags={"Videos"},
     *     security={{"sanctum": {}}},
     *     summary="Create a new video with tags",
     *     description="Adds a new video to the database with optional tags",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"furniture_type", "url"},
     *                 @OA\Property(property="title", type="string", example="Sample Video"),
     *                 @OA\Property(property="furniture_type", type="string", example="Sample Video"),
     *                 @OA\Property(property="description", type="string", example="This is a sample video description."),
     *                 @OA\Property(
     *                     property="url",
     *                     type="string",
     *                     format="binary",
     *                     description="Video file upload (e.g., mp4)"
     *                 ),
     *                 @OA\Property(property="price", type="number", format="float", example="10.50"),
     *                 @OA\Property(property="sizes", type="string", example="1920x1080"),
     *                 @OA\Property(property="is_fixed", type="integer", enum={1, 0}, example=0),
     *                 @OA\Property(property="in_stock", type="integer", enum={1, 0}, example=0),
     *                 @OA\Property(
     *                     property="preview_url",
     *                     type="string",
     *                     format="binary",
     *                     description="Preview image file upload (e.g., jpeg, png)"
     *                 ),
     *                 @OA\Property(
     *                     property="tags[]",
     *                     type="array",
     *                     description="Array of tag IDs to associate with the video",
     *                     @OA\Items(type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Video created",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Sample Video"),
     *             @OA\Property(property="furniture_type", type="string", example="Sample Video"),
     *             @OA\Property(property="description", type="string", example="This is a sample video description."),
     *             @OA\Property(property="url", type="string", example="videos/videos/sample.mp4"),
     *             @OA\Property(property="price", type="number", format="float", example="10.50"),
     *             @OA\Property(property="sizes", type="string", example="1920x1080"),
     *             @OA\Property(property="is_fixed", type="boolean", example=true),
     *             @OA\Property(property="preview_url", type="string", example="photos/videos/sample-preview.jpg"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Technology")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        // print($request->file('url')->getMimeType());
        // phpinfo();
        $video_count = Video::where('user_id',auth()->id())
        ->count();
        $user_video_count = User::where('id',auth()->id())
        ->first();
        $video_default_count = Variable::where('name','video_default_count')->first();

//        if($video_count>=$video_default_count->value + $user_video_count->videos_count)
//            return response()->json('Вы превисели количество загружаемых видео', Response::HTTP_BAD_REQUEST);

        $validatedData = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'furniture_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|mimetypes:video/quicktime,video/mp4,video/webm,video/x-flv,video/mpeg,video/x-ms-asf,application/x-mpegURL,video/MP2T,video/3gpp,video/x-msvideo,video/x-ms-wmv|file|max:204800',
            // 'url' => 'required|mimes:x-flv,mp4,x-mpegURL,MP2T,3gpp,quicktime,x-msvideo,x-ms-wmv|file|max:204800', // Ограничение на видеофайл
            'price' => 'nullable|numeric',
            'sizes' => 'nullable|string',
            'is_fixed' => 'nullable|boolean',
            'in_stock' => 'nullable|boolean',
            'preview_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
            'tags' => 'nullable|array', // Массив тегов
            'tags.*' => 'nullable|exists:tags,id', // Каждый тег должен существовать в таблице tags

        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $validatedData = $validatedData->validated();
        // dd($validatedData['tags']);

        $validatedData['user_id'] = auth()->id();
        $video = new Video($validatedData);

        // Загрузка видеофайла
        if ($request->hasFile('url')) {
            $originalPath  = $request->file('url')->store('videos/videos', 'public'); // сохранение в storage/app/public/photos/users
            $video->url = $originalPath;
        }

        // Загрузка превью (если присутствует)
        if ($request->hasFile('preview_url')) {
            $video->preview_url = $request->file('preview_url')->store('photos/videos', 'public');
        }
        $video->save();

        $tags = [];

        if (isset($validatedData['tags'][0]) && !empty($validatedData['tags'][0])) {
            $tags = explode(',', $validatedData['tags'][0]);
        }
//        dd($tags);
        // Присваиваем теги к видео
        if (!empty($tags)) {
            foreach ($tags as $tagId) {
                $video->tags()->attach($tagId);
            }
        }
        $video = $this->show($video->id)->original;
        return response()->json($video, Response::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     *     path="/api/videos/{id}",
     *     tags={"Videos"},
     *     security={{"sanctum": {}}},
     *     summary="Update video information",
     *     description="Updates a video record by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the video to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"_method"},
     *                 @OA\Property(
     *                     property="_method",
     *                     type="string",
     *                     example="PUT",
     *                     description="Hidden field to simulate PUT method"
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     description="Video title"
     *                 ),
     *                 @OA\Property(
     *                     property="furniture_type",
     *                     type="string",
     *                     description="Furniture type associated with the video"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Description of the video"
     *                 ),
     *                 @OA\Property(
     *                     property="url",
     *                     type="string",
     *                     format="binary",
     *                     description="Video file (mp4, webm, etc.)"
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="number",
     *                     format="float",
     *                     description="Price associated with the video"
     *                 ),
     *                 @OA\Property(
     *                     property="sizes",
     *                     type="string",
     *                     description="Sizes related to the video"
     *                 ),
     *                 @OA\Property(
     *                     property="is_fixed",
     *                     type="integer",
     *                     enum={1, 0},
     *                     example=0,
     *                     description="Whether the video is fixed or not"
     *                 ),
     *                 @OA\Property(
     *                     property="is_visible",
     *                     type="integer",
     *                     enum={1, 0},
     *                     example=0,
     *                     description="Visibility status of the video"
     *                 ),
     *                 @OA\Property(
     *                     property="in_stock",
     *                     type="integer",
     *                     enum={1, 0},
     *                     example=0,
     *                     description="Visibility status of the video"
     *                 ),
     *                 @OA\Property(
     *                     property="preview_url",
     *                     type="string",
     *                     format="binary",
     *                     description="Preview image file (jpg, png, etc.)"
     *                 ),
     *                 @OA\Property(
     *                     property="tags[]",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     description="Array of tag IDs"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Video created",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Sample Video"),
     *             @OA\Property(property="furniture_type", type="string", example="Sample Video"),
     *             @OA\Property(property="description", type="string", example="This is a sample video description."),
     *             @OA\Property(property="url", type="string", example="videos/videos/sample.mp4"),
     *             @OA\Property(property="price", type="number", format="float", example="10.50"),
     *             @OA\Property(property="sizes", type="string", example="1920x1080"),
     *             @OA\Property(property="is_fixed", type="boolean", example=true),
     *             @OA\Property(property="preview_url", type="string", example="photos/videos/sample-preview.jpg"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Technology")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Video not found")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json(['message' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'furniture_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|mimetypes:video/quicktime,video/mp4,video/webm,video/x-flv,video/mpeg,video/x-ms-asf,application/x-mpegURL,video/MP2T,video/3gpp,video/x-msvideo,video/x-ms-wmv|file|max:204800',
            'price' => 'nullable|numeric',
            'sizes' => 'nullable|string',
            'is_fixed' => 'nullable|boolean',
            'in_stock' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'preview_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|exists:tags,id',
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $validatedData = $validatedData->validated();

        // Обновление основных полей
        $video->fill($validatedData);

        // Загрузка видеофайла
        if ($request->hasFile('url')) {
            $originalPath = $request->file('url')->store('videos/videos', 'public');
            $originalFullPath = storage_path('app/public/' . $originalPath);

            // $compressedPath = 'videos/videos/compressed/' . pathinfo($originalPath, PATHINFO_FILENAME) . '_compressed.mp4';
            // $compressedFullPath = storage_path('app/public/' . $compressedPath);

            // $ffmpeg = FFMpeg::create(['timeout' => 3600]);
            // $video_ff = $ffmpeg->open($originalFullPath);
            // $video_ff->filters()
            //     ->framerate(new FrameRate(30), 0)
            //     ->resize(new Dimension(1920, 1080), ResizeFilter::RESIZEMODE_INSET)
            //     ->synchronize();
            // $format = new X264('aac', 'libx264');
            // $format->setKiloBitrate(5000);
            // $video_ff->save($format, $compressedFullPath);

            if (File::exists($originalFullPath)) {
                File::delete($originalFullPath);
            }

            $video->url = $originalPath;
            // $video->url = $compressedPath;

        }


        // Обновление превью
        if ($request->hasFile('preview_url')) {
            $previewPath = $request->file('preview_url')->store('photos/videos', 'public');
            $video->preview_url = $previewPath;
        }

        // Обновление тегов
        if (isset($validatedData['tags'])) {
            $tags = !empty($validatedData['tags']) ? explode(',', $validatedData['tags'][0]) : [];
            $video->tags()->sync($tags);
        }

        $video->save();

        return response()->json($video, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/videos/{id}",
     *     tags={"Videos"},
     *     security={{"sanctum": {}}},
     *     summary="Delete a video",
     *     description="Deletes a video by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the video to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Video deleted"),
     *     @OA\Response(response=404, description="Video not found")
     * )
     */
    public function destroy($id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json(['message' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        $video->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

}
// ffmpeg -i /var/www/supermaker/storage/app/public/videos/videos/ZLfquSumxMAbyyfAHhc6cdO6B4ReZ2LWr5ihKncn.mp4 -vf "scale='if(gt(iw/ih,1920/1080),1920,-1):if(gt(iw/ih,1920/1080),-1,1080)'" -c:v libx264 -preset fast -crf 23 -c:a aac -b:a 128k /var/www/supermaker/storage/app/public/videos/videos/ZLfquSumxMAbyyfAHhc6cdO6B4ReZ2LWr5ihKncn-2.mp4
