<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PaymentStatusController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/payment-statuses",
     *     tags={"Payment Statuses"},
     *     summary="Get a list of payment statuses",
     *     description="Returns a list of all payment statuses.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $statuses = PaymentStatus::all();
        return response()->json($statuses, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/payment-statuses/{id}",
     *     tags={"Payment Statuses"},
     *     summary="Get a payment status by ID",
     *     description="Returns a single payment status",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of payment status to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment status found"
     *     ),
     *     @OA\Response(response=404, description="Payment status not found")
     * )
     */
    public function show($id)
    {
        $status = PaymentStatus::find($id);

        if (!$status) {
            return response()->json(['message' => 'Payment status not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($status, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/payment-statuses",
     *     tags={"Payment Statuses"},
     *     summary="Create a new payment status",
     *     description="Adds a new payment status to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Paid"),
     *             @OA\Property(property="description", type="string", example="Payment has been completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment status created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:payment_statuses',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $status = PaymentStatus::create($validatedData->validated());
        return response()->json($status, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/payment-statuses/{id}",
     *     tags={"Payment Statuses"},
     *     summary="Update an existing payment status",
     *     description="Updates payment status details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of payment status to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Paid Updated"),
     *             @OA\Property(property="description", type="string", example="Payment has been completed and verified")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment status updated"
     *     ),
     *     @OA\Response(response=404, description="Payment status not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $status = PaymentStatus::find($id);

        if (!$status) {
            return response()->json(['message' => 'Payment status not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'string|max:255|unique:payment_statuses,name,' . $status->id,
            'description' => 'nullable|string|max:500',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $status->update($validatedData->validated());
        return response()->json($status, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/payment-statuses/{id}",
     *     tags={"Payment Statuses"},
     *     summary="Delete a payment status",
     *     description="Deletes a payment status by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of payment status to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Payment status deleted"),
     *     @OA\Response(response=404, description="Payment status not found")
     * )
     */
    public function destroy($id)
    {
        $status = PaymentStatus::find($id);

        if (!$status) {
            return response()->json(['message' => 'Payment status not found'], Response::HTTP_NOT_FOUND);
        }

        $status->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
