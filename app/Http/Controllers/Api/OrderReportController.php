<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReport;
use Illuminate\Http\Request;

class OrderReportController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/order_reports",
     *     tags={"Order Reports"},
     *     security={{"sanctum": {}}},
     *     summary="Get all order reports",
     *     description="Retrieve a list of all order reports.",
     *     @OA\Response(
     *         response=200,
     *         description="List of order reports"
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index()
    {
        $reports = OrderReport::all();
        return response()->json($reports);
    }

    /**
     * @OA\Post(
     *     path="/api/order_reports",
     *     tags={"Order Reports"},
     *     security={{"sanctum": {}}},
     *     summary="Create a new order report with photos",
     *     description="Uploads photos to the server, saves their URLs, and creates a report associated with an order.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"order_id", "photos"},
     *                 @OA\Property(
     *                     property="order_id",
     *                     type="integer",
     *                     description="ID of the associated order",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="photos[]",
     *                     type="array",
     *                     description="Array of photo files to upload",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary",
     *                         description="Photo file (jpeg, png, jpg, gif)"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Optional description for the report",
     *                     example="This is a sample report description."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Report created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Report created successfully."),
     *             @OA\Property(
     *                 property="report",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="photo_urls",
     *                     type="array",
     *                     description="Array of uploaded photo URLs",
     *                     @OA\Items(type="string", example="http://example.com/storage/photos/photo1.jpg")
     *                 ),
     *                 @OA\Property(property="description", type="string", example="This is a sample report description."),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2024-12-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2024-12-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 additionalProperties={
     *                     @OA\Property(type="array", @OA\Items(type="string"))
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'photos' => 'required|array', // Ожидается массив файлов
            'photos.*' => 'file|mimes:jpeg,png,jpg,gif|max:2048', // Валидация каждого файла
            'description' => 'nullable|string',
        ]);

        $photoUrls = []; // Для хранения ссылок на сохраненные фотографии

        // Сохранение каждой фотографии
        foreach ($request->file('photos') as $photo) {
            // Генерация уникального имени файла
            $fileName = uniqid() . '.' . $photo->getClientOriginalExtension();

            // Сохранение файла в папку public/storage/photos
            $filePath = $photo->storeAs('photos', $fileName, 'public');

            // Добавление ссылки на файл в массив
            $photoUrls[] = asset('storage/' . $filePath);
        }

        // Создание записи в базе данных
        $report = OrderReport::create([
            'order_id' => $request->order_id,
            'photo_urls' => $photoUrls, // Массив ссылок
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Report created successfully.',
            'report' => $report,
        ], 201);
    }

/**
 * @OA\Get(
 *     path="/api/order_reports/{id}",
 *     tags={"Order Reports"},
 *     security={{"sanctum": {}}},
 *     summary="Get a specific order report",
 *     description="Retrieve a specific order report by its ID.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Order report ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Order report details"
 *     ),
 *     @OA\Response(response=404, description="Order report not found"),
 *     @OA\Response(response=401, description="Unauthorized")
 * )
 */
    public function show($id)
    {
        $report = OrderReport::findOrFail($id);
        return response()->json($report);
    }

/**
 * @OA\Post(
 *     path="/api/order_reports/{id}",
 *     tags={"Order Reports"},
 *     security={{"sanctum": {}}},
 *     summary="Update an order report with new photos",
 *     description="Uploads new photos to the server, deletes old photos, and updates the report with the new photo URLs. This is a POST request with _method=PUT.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the report to update",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"photo_urls"},
 *                 @OA\Property(
 *                     property="photo_urls",
 *                     type="array",
 *                     description="Array of new photo files to upload",
 *                     @OA\Items(
 *                         type="string",
 *                         format="binary",
 *                         description="Photo file (jpeg, png, jpg, gif)"
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     type="string",
 *                     description="Optional description for the report",
 *                     example="Updated report description."
 *                 ),
 *                 @OA\Property(
 *                     property="_method",
 *                     type="string",
 *                     description="Override the HTTP method to PUT",
 *                     example="PUT"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Report updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Report updated successfully."),
 *             @OA\Property(
 *                 property="report",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="order_id", type="integer", example=1),
 *                 @OA\Property(
 *                     property="photo_urls",
 *                     type="array",
 *                     description="Array of uploaded photo URLs",
 *                     @OA\Items(type="string", example="http://example.com/storage/photos/photo1.jpg")
 *                 ),
 *                 @OA\Property(property="description", type="string", example="Updated report description."),
 *                 @OA\Property(property="created_at", type="string", format="datetime", example="2024-12-01T12:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2024-12-01T12:00:00Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 additionalProperties={
 *                     @OA\Property(type="array", @OA\Items(type="string"))
 *                 }
 *             )
 *         )
 *     )
 * )
 */
public function update(Request $request, $id)
{
    $report = OrderReport::findOrFail($id);

    // Валидируем данные запроса
    $request->validate([
        'photo_urls' => 'sometimes|array',
        'photo_urls.*' => 'string|url',
        'description' => 'nullable|string',
    ]);

    // Удаляем старые фотографии с сервера
    if ($report->photo_urls) {
        foreach ($report->photo_urls as $photo_url) {
            $photo_path = public_path(str_replace('storage', 'app/public', $photo_url));
            if (file_exists($photo_path)) {
                unlink($photo_path);  // Удаление фото с сервера
            }
        }
    }

    // Загружаем новые фотографии
    $new_photo_urls = [];
    if ($request->hasFile('photo_urls')) {
        foreach ($request->file('photo_urls') as $photo) {
            $path = $photo->store('photos', 'public'); // Сохраняем фото в папку storage/app/public/photos
            $new_photo_urls[] = asset('storage/' . $path);  // Сохраняем ссылку на фото
        }
    }

    // Обновляем отчет с новыми данными
    $report->update([
        'photo_urls' => $new_photo_urls,
        'description' => $request->description,
    ]);

    return response()->json([
        'message' => 'Report updated successfully.',
        'report' => $report,
    ]);
}

/**
 * @OA\Delete(
 *     path="/api/order_reports/{id}",
 *     tags={"Order Reports"},
 *     security={{"sanctum": {}}},
 *     summary="Delete an order report",
 *     description="Remove an existing order report by its ID.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Order report ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Report deleted successfully"
 *     ),
 *     @OA\Response(response=404, description="Order report not found"),
 *     @OA\Response(response=401, description="Unauthorized")
 * )
 */
    public function destroy($id)
    {
        $report = OrderReport::findOrFail($id);
        $report->delete();

        return response()->json([
            'message' => 'Report deleted successfully.',
        ]);
    }
}
