<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus; // Ensure you have an OrderStatus model
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class OrderStatusController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/order_statuses",
     *     tags={"Order Statuses"},
     *     security={{"sanctum": {}}},
     *     summary="Get a list of order statuses",
     *     description="Returns a list of all order statuses.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $orderStatuses = OrderStatus::all();
        return response()->json($orderStatuses, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/order_statuses/{id}",
     *     tags={"Order Statuses"},
     *     security={{"sanctum": {}}},
     *     summary="Get an order status by ID",
     *     description="Returns a single order status",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order status to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status found"
     *     ),
     *     @OA\Response(response=404, description="Order status not found")
     * )
     */
    public function show($id)
    {
        $orderStatus = OrderStatus::find($id);

        if (!$orderStatus) {
            return response()->json(['message' => 'Order status not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($orderStatus, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/order_statuses",
     *     tags={"Order Statuses"},
     *     security={{"sanctum": {}}},
     *     summary="Create a new order status",
     *     description="Adds a new order status to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name_for_user","name_for_master"},
     *             @OA\Property(property="name_for_user", type="string", example="Pending", description="Name of the order status"),
     *             @OA\Property(property="name_for_master", type="string", example="Pending", description="Name of the order status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order status created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name_for_user' => 'required|string|max:255|unique:order_statuses,name_for_user',
            'name_for_master' => 'required|string|max:255|unique:order_statuses,name_for_master',
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $orderStatus = OrderStatus::create($validatedData->validated());
        return response()->json($orderStatus, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/order_statuses/{id}",
     *     tags={"Order Statuses"},
     *     security={{"sanctum": {}}},
     *     summary="Update an existing order status",
     *     description="Updates order status details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order status to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name_for_user","name_for_master"},
     *             @OA\Property(property="name_for_user", type="string", example="Shipped", description="Updated name of the order status"),
     *             @OA\Property(property="name_for_master", type="string", example="Shipped", description="Updated name of the order status"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated"
     *     ),
     *     @OA\Response(response=404, description="Order status not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $orderStatus = OrderStatus::find($id);

        if (!$orderStatus) {
            return response()->json(['message' => 'Order status not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'name_for_user' => 'nullable|string|max:255|unique:order_statuses,name_for_user,' . $orderStatus->id,
            'name_for_master' => 'nullable|string|max:255|unique:order_statuses,name_for_master,' . $orderStatus->id,
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $orderStatus->update(array_filter($validatedData->validated())); // Update only provided fields
        return response()->json($orderStatus, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/order_statuses/{id}",
     *     tags={"Order Statuses"},
     *     security={{"sanctum": {}}},
     *     summary="Delete an order status",
     *     description="Deletes an order status by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order status to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Order status deleted"),
     *     @OA\Response(response=404, description="Order status not found")
     * )
     */
    public function destroy($id)
    {
        $orderStatus = OrderStatus::find($id);

        if (!$orderStatus) {
            return response()->json(['message' => 'Order status not found'], Response::HTTP_NOT_FOUND);
        }

        $orderStatus->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
