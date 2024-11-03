<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/payments",
     *     tags={"Payments"},
     *     summary="Get a list of payments",
     *     description="Returns a list of all payments.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $payments = Payment::all();
        return response()->json($payments, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/payments/{id}",
     *     tags={"Payments"},
     *     summary="Get a payment by ID",
     *     description="Returns a single payment",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of payment to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment found"
     *     ),
     *     @OA\Response(response=404, description="Payment not found")
     * )
     */
    public function show($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($payment, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/payments",
     *     tags={"Payments"},
     *     summary="Create a new payment",
     *     description="Adds a new payment to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "user_id", "payment_status_id"},
     *             @OA\Property(property="amount", type="number", format="float", example=100.50),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="payment_status_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Payment for services")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'user_id' => 'required|integer|exists:users,id',
            'payment_status_id' => 'required|integer|exists:payment_statuses,id',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $payment = Payment::create($validatedData->validated());
        return response()->json($payment, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/payments/{id}",
     *     tags={"Payments"},
     *     summary="Update an existing payment",
     *     description="Updates payment details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of payment to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", format="float", example=150.75),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="payment_status_id", type="integer", example=2),
     *             @OA\Property(property="description", type="string", example="Updated payment description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated"
     *     ),
     *     @OA\Response(response=404, description="Payment not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0',
            'user_id' => 'nullable|integer|exists:users,id',
            'payment_status_id' => 'nullable|integer|exists:payment_statuses,id',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $payment->update($validatedData->validated());
        return response()->json($payment, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/payments/{id}",
     *     tags={"Payments"},
     *     summary="Delete a payment",
     *     description="Deletes a payment by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of payment to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Payment deleted"),
     *     @OA\Response(response=404, description="Payment not found")
     * )
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        $payment->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
