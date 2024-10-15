<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Документация API",
 *     version="1.0.0",
 *     description="Описание вашего API"
 * )
 */
 
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
