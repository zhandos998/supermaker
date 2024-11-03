<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentStatusController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\CompanyTypeController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\VariableController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\OrderStatusController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\QuetionController;
use App\Http\Controllers\Api\DefaultAnswerController;
use App\Http\Controllers\Api\UserAnswerController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'show']);

Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'reset']);

Route::middleware('auth:sanctum')->resource('users', UserController::class);
Route::middleware('auth:sanctum')->resource('cities', CityController::class);
Route::middleware('auth:sanctum')->resource('countries', CountryController::class);
Route::middleware('auth:sanctum')->resource('payments', PaymentController::class);
Route::middleware('auth:sanctum')->resource('payment_statuses', PaymentStatusController::class);
Route::middleware('auth:sanctum')->resource('stores', StoreController::class);
Route::middleware('auth:sanctum')->resource('company_types', CompanyTypeController::class);
Route::middleware('auth:sanctum')->resource('roles', RoleController::class);

Route::middleware('auth:sanctum')->resource('tags', TagController::class);
Route::middleware('auth:sanctum')->resource('videos', VideoController::class);
Route::middleware('auth:sanctum')->resource('variables', VariableController::class);
Route::middleware('auth:sanctum')->resource('ratings', RatingController::class);
Route::middleware('auth:sanctum')->resource('order_statuses', OrderStatusController::class);

Route::middleware('auth:sanctum')->resource('orders', OrderController::class);
Route::middleware('auth:sanctum')->resource('favorites', FavoriteController::class);
Route::middleware('auth:sanctum')->resource('surveys', SurveyController::class);
Route::middleware('auth:sanctum')->resource('quetions', QuetionController::class);
Route::middleware('auth:sanctum')->resource('default_answers', DefaultAnswerController::class);
Route::middleware('auth:sanctum')->resource('user_answers', UserAnswerController::class);

