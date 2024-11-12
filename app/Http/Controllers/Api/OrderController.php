<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order; // Ensure you have an Order model
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Get a list of orders",
     *     description="Returns a list of all orders.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $orders = Order::all();
        return response()->json($orders, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     tags={"Orders"},
     *     summary="Get an order by ID",
     *     description="Returns a single order",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order found"
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function show($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($order, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     description="Adds a new order to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_id", "status_id", "total"},
     *             @OA\Property(property="customer_id", type="integer", description="ID of the customer"),
     *             @OA\Property(property="status_id", type="integer", description="ID of the order status"),
     *             @OA\Property(property="total", type="number", format="float", description="Total amount of the order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:users,id',
            'status_id' => 'required|integer|exists:order_statuses,id',
            'total' => 'required|numeric|min:0',
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $order = Order::create($validatedData->validated());

        return response()->json($order, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{id}",
     *     tags={"Orders"},
     *     summary="Update an existing order",
     *     description="Updates order details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status_id", type="integer", description="Updated status ID"),
     *             @OA\Property(property="total", type="number", format="float", description="Updated total amount")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated"
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'status_id' => 'sometimes|integer|exists:order_statuses,id',
            'total' => 'sometimes|numeric|min:0',
            'items' => 'sometimes|array',
            'items.*.product_id' => 'sometimes|integer|exists:products,id',
            'items.*.quantity' => 'sometimes|integer|min:1',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $order->update(array_filter($validatedData->validated())); // Update only provided fields
        return response()->json($order, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     tags={"Orders"},
     *     summary="Delete an order",
     *     description="Deletes an order by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Order deleted"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $order->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
