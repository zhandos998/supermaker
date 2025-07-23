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
     *     security={{"sanctum": {}}},
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
     *     security={{"sanctum": {}}},
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
     *     security={{"sanctum": {}}},
     *     summary="Create a new payment",
     *     description="Adds a new payment to the database with an uploaded PDF file",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"amount", "user_id", "payment_status_id", "file"},
     *                 @OA\Property(property="amount", type="number", format="float", example=100.50),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="payment_status_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="PDF document to upload"
     *                 ),
     *                 @OA\Property(property="description", type="string", example="Payment for services")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment created",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=100.50),
     *             @OA\Property(property="status_id", type="integer", example=1),
     *             @OA\Property(property="check_url", type="string", example="http://example.com/storage/payments/document.pdf"),
     *             @OA\Property(property="description", type="string", example="Payment for services"),
     *             @OA\Property(property="created_at", type="string", format="datetime", example="2024-12-05T10:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="datetime", example="2024-12-05T10:00:00Z")
     *         )
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
            'file' => 'required|file|mimes:pdf|max:10240', // Проверка загружаемого файла (PDF, до 10MB)
            'description' => 'nullable|string|max:255',
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $filePath = $request->file('file')->store('documents/payments', 'public');
        $fileUrl = asset('storage/' . $filePath);

        // Создаем запись в таблице payments
        $payment = Payment::create([
            'amount' => $request->amount,
            'user_id' => $request->user_id,
            'status_id' => $request->payment_status_id,
            'check_url' => $fileUrl,
            'description' => $request->description,
        ]);

        return response()->json($payment, Response::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/{id}",
     *     tags={"Payments"},
     *     security={{"sanctum": {}}},
     *     summary="Update an existing payment (simulated PUT via POST)",
     *     description="Updates payment details by ID using POST with a hidden _method parameter",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of payment to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"_method"},
     *                 @OA\Property(
     *                     property="_method",
     *                     type="string",
     *                     example="PUT",
     *                     description="Hidden field to simulate PUT method"
     *                 ),
     *                 @OA\Property(property="amount", type="number", format="float", example=150.75),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="payment_status_id", type="integer", example=2),
     *                 @OA\Property(property="description", type="string", example="Updated payment description"),
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional PDF document to replace the existing one"
     *                 )
     *             )
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
            'file' => 'nullable|file|mimes:pdf|max:10240', // Проверка загружаемого файла
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        // Если загружен новый файл
        if ($request->hasFile('file')) {
            // Удаляем старый файл, если он существует
            if ($payment->check_url) {
                $oldFilePath = str_replace(asset('storage'), '', $payment->check_url);
                if (file_exists(storage_path('app/public' . $oldFilePath))) {
                    unlink(storage_path('app/public' . $oldFilePath));
                }
            }

            // Сохраняем новый файл
            $filePath = $request->file('file')->store('payments', 'public');
            $payment->check_url = asset('storage/' . $filePath);
        }


        $payment->fill($validatedData->validated());
        $payment->save();
        // Обновляем остальные поля
        // $payment->update($validatedData->except(['_method'])); // Исключаем _method
        return response()->json($payment, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/payments/{id}",
     *     tags={"Payments"},
     *     security={{"sanctum": {}}},
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
