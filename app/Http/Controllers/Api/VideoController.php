<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/videos",
     *     tags={"Videos"},
     *     summary="Get a list of videos",
     *     description="Returns a list of all videos.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $videos = Video::all();
        return response()->json($videos, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/videos/{id}",
     *     tags={"Videos"},
     *     summary="Get a video by ID",
     *     description="Returns a single video",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the video to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video found"
     *     ),
     *     @OA\Response(response=404, description="Video not found")
     * )
     */
    public function show($id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json(['message' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($video, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/videos",
     *     tags={"Videos"},
     *     summary="Create a new video",
     *     description="Adds a new video to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "url"},
     *             @OA\Property(property="title", type="string", example="Sample Video"),
     *             @OA\Property(property="description", type="string", example="This is a sample video description."),
     *             @OA\Property(property="url", type="string", example="http://example.com/sample-video.mp4"),
     *             @OA\Property(property="thumbnail", type="string", format="binary", description="Thumbnail image file upload")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Video created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Thumbnail validation
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $validatedData['thumbnail'] = $thumbnailPath;
        }

        $video = Video::create($validatedData->validated());
        return response()->json($video, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/videos/{id}",
     *     tags={"Videos"},
     *     summary="Update an existing video",
     *     description="Updates video details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the video to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Video Title"),
     *             @OA\Property(property="description", type="string", example="This is an updated video description."),
     *             @OA\Property(property="url", type="string", example="http://example.com/updated-video.mp4"),
     *             @OA\Property(property="thumbnail", type="string", format="binary", description="Updated thumbnail file upload")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video updated"
     *     ),
     *     @OA\Response(response=404, description="Video not found")
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
            'description' => 'nullable|string',
            'url' => 'nullable|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Thumbnail validation
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $validatedData['thumbnail'] = $thumbnailPath;
        }

        $video->update($validatedData->validated());
        return response()->json($video, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/videos/{id}",
     *     tags={"Videos"},
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
