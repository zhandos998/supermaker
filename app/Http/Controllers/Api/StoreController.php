<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/stores",
     *     tags={"Stores"},
     *     summary="Get a list of stores",
     *     description="Returns a list of all stores.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $stores = Store::all();
        return response()->json($stores, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/stores/{id}",
     *     tags={"Stores"},
     *     summary="Get a store by ID",
     *     description="Returns a single store",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of store to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store found"
     *     ),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
    public function show($id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($store, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/stores",
     *     tags={"Stores"},
     *     summary="Create a new store",
     *     description="Adds a new store to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "location"},
     *             @OA\Property(property="name", type="string", example="SuperMart"),
     *             @OA\Property(property="location", type="string", example="123 Main St"),
     *             @OA\Property(property="description", type="string", example="A local grocery store"),
     *             @OA\Property(property="owner_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Store created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:stores',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'owner_id' => 'nullable|integer|exists:users,id', // Assuming stores can have an owner
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $store = Store::create($validatedData->validated());
        return response()->json($store, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/stores/{id}",
     *     tags={"Stores"},
     *     summary="Update an existing store",
     *     description="Updates store details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of store to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="SuperMart Updated"),
     *             @OA\Property(property="location", type="string", example="456 Main St"),
     *             @OA\Property(property="description", type="string", example="An updated local grocery store"),
     *             @OA\Property(property="owner_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store updated"
     *     ),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'string|max:255|unique:stores,name,' . $store->id,
            'location' => 'string|max:255',
            'description' => 'nullable|string|max:500',
            'owner_id' => 'nullable|integer|exists:users,id',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $store->update($validatedData->validated());
        return response()->json($store, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/stores/{id}",
     *     tags={"Stores"},
     *     summary="Delete a store",
     *     description="Deletes a store by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of store to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Store deleted"),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
    public function destroy($id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], Response::HTTP_NOT_FOUND);
        }

        $store->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
