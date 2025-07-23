<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File; // Ensure you have a File model
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/files",
     *     tags={"Files"},
     *     security={{"sanctum": {}}},
     *     summary="Get a list of files",
     *     description="Returns a list of all file items for the authenticated user.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
    *     @OA\Response(
    *         response=401,
    *         description="Unauthorized"
    *     )
     * )
     */
    public function index()
    {

        $files = File::all();

        // Возвращаем результат
        return response()->json($files, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/files",
     *     tags={"Files"},
     *     security={{"sanctum": {}}},
     *     summary="Add a file item",
     *     description="Adds a new item to the user's files list",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "file"},
     *                 @OA\Property(property="name", type="string", description="Name of the file"),
     *                 @OA\Property(property="file", type="string", format="binary", description="File to upload")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="File item created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        // Валидация входящих данных
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'file' => 'required|file', // Ожидается файл
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        // Сохранение файла в хранилище
        $filePath = $request->file('file')->store('files', 'public');

        // Сохранение записи в базу данных
        $file = File::create([
            'name' => $request->input('name'),
            'path' => 'https://supermakers.pro/storage/'.$filePath,
        ]);

        return response()->json($file, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/files/{id}",
     *     tags={"Files"},
     *     security={{"sanctum": {}}},
     *     summary="Get a file item",
     *     description="Retrieve details of a file item by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the file to retrieve"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File item retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="File ID"),
     *             @OA\Property(property="name", type="string", description="File name"),
     *             @OA\Property(property="path", type="string", description="Path to the file"),
     *             @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
     *         )
     *     ),
     *     @OA\Response(response=404, description="File not found")
     * )
     */
    public function show($id)
    {
        // Поиск файла в таблице
        $file = File::find($id);

        if (!$file) {
            return response()->json(['message' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        // Возвращение данных о файле
        return response()->json($file, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/files/{id}",
     *     tags={"Files"},
     *     security={{"sanctum": {}}},
     *     summary="Update a file item with _method=PUT",
     *     description="Updates the details of a file item by its ID using POST method with _method=PUT",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the file to update"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"_method", "name", "file"},
     *                 @OA\Property(property="_method", type="string", description="HTTP method override (PUT)", example="PUT"),
     *                 @OA\Property(property="name", type="string", description="Updated name of the file"),
     *                 @OA\Property(property="file", type="string", format="binary", description="Updated file")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File item updated"
     *     ),
     *     @OA\Response(response=404, description="File not found"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function update(Request $request, $id)
    {
        // Поиск записи в таблице
        $file = File::find($id);

        if (!$file) {
            return response()->json(['message' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        // Валидация данных
        $validatedData = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'file' => 'nullable|file', // Файл не обязателен
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        // Обновление имени файла, если передано
        if ($request->has('name')) {
            $file->name = $request->input('name');
        }

        // Обновление файла, если передан новый
        if ($request->hasFile('file')) {
            // Удаляем старый файл
            if (Storage::exists('public/' . $file->path)) {
                Storage::delete('public/' . $file->path);
            }

            // Сохраняем новый файл
            $filePath = $request->file('file')->store('files', 'public');
            $file->path = 'https://supermakers.pro/storage/'.$filePath;
        }

        // Сохраняем изменения
        $file->save();

        return response()->json($file, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/files/{id}",
     *     tags={"Files"},
     *     security={{"sanctum": {}}},
     *     summary="Delete a file item",
     *     description="Deletes a file item by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the file to delete"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File item deleted"
     *     ),
     *     @OA\Response(response=404, description="File not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function destroy($id)
    {
        // Поиск записи в базе данных
        $file = File::find($id);

        if (!$file) {
            return response()->json(['message' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            // Удаление файла из хранилища
            if (Storage::exists('public/' . $file->path)) {
                Storage::delete('public/' . $file->path);
            }

            // Удаление записи из базы данных
            $file->delete();

            return response()->json(['message' => 'File item deleted'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete file', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
