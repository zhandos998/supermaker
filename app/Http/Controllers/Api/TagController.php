<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tags",
     *     tags={"Tags"},
     *     summary="Get a list of tags",
     *     description="Returns a list of all tags.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $tags = Tag::all();
        return response()->json($tags, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/tags/{id}",
     *     tags={"Tags"},
     *     summary="Get a tag by ID",
     *     description="Returns a single tag",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the tag to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag found"
     *     ),
     *     @OA\Response(response=404, description="Tag not found")
     * )
     */
    public function show($id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($tag, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/tags",
     *     tags={"Tags"},
     *     summary="Create a new tag",
     *     description="Adds a new tag to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="New Tag"),
     *             @OA\Property(property="description", type="string", example="This is a new tag")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tag created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $tag = Tag::create($validatedData->validated());
        return response()->json($tag, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/tags/{id}",
     *     tags={"Tags"},
     *     summary="Update an existing tag",
     *     description="Updates tag details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the tag to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Tag"),
     *             @OA\Property(property="description", type="string", example="This is an updated tag description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag updated"
     *     ),
     *     @OA\Response(response=404, description="Tag not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255|unique:tags,name,' . $id,
            'description' => 'nullable|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $tag->update($validatedData->validated());
        return response()->json($tag, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/tags/{id}",
     *     tags={"Tags"},
     *     summary="Delete a tag",
     *     description="Deletes a tag by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the tag to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Tag deleted"),
     *     @OA\Response(response=404, description="Tag not found")
     * )
     */
    public function destroy($id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }

        $tag->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
