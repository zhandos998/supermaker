<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\RegionController;
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
use App\Http\Controllers\Api\OrderReportController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\DefaultAnswerController;
use App\Http\Controllers\Api\OptionsController;
use App\Http\Controllers\Api\QuestionTypesController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UserAnswerController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\LikeController;


Route::get('/countries', [CountryController::class, 'index']);
Route::get('/regions', [RegionController::class, 'index']);     // ?country_id=1
Route::get('/cities', [CityController::class, 'index']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/send-verification-code', [AuthController::class, 'sendVerificationCode']);

Route::post('/verify-account', [AuthController::class, 'verifyAccount']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);

//Route::post('/password/reset-link', [AuthController::class, 'sendResetLink']);
//Route::post('/password/reset', [AuthController::class, 'reset']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'show_current_user']);

// ----------------------------------------------------------------------------------------------------

Route::middleware('auth:sanctum')->get('countries/{id}/regions-cities', [CountryController::class, 'showWithRegionsAndCities']);
Route::middleware('auth:sanctum')->post('default_answers/GetDefaultAnswersByVideoID', [DefaultAnswerController::class,'GetDefaultAnswersByVideoID']);
Route::middleware('auth:sanctum')->post('default_answers/AddDefaultAnswersWithImages', [DefaultAnswerController::class,'AddDefaultAnswersWithImages']);

Route::middleware('auth:sanctum')->post('user_answers/GetUserAnswersByVideoID', [UserAnswerController::class,'GetUserAnswersByVideoID']);
Route::middleware('auth:sanctum')->post('user_answers/SetUserAnswerImage', [UserAnswerController::class,'SetUserAnswerImage']);
Route::middleware('auth:sanctum')->post('user_answers/AddUserAnswersWithImages', [UserAnswerController::class,'AddUserAnswersWithImages']);

Route::middleware('auth:sanctum')->get('surveys/GetSurveyWithDefaultAnswers/{video_id}', [SurveyController::class,'GetSurveyWithDefaultAnswers']);

Route::get('videos/GetVideosByTags', [VideoController::class, 'GetVideosByTags']);

Route::middleware('auth:sanctum')->post('orders/quick_order/{user_survey_id}', [OrderController::class, 'QuickOrder']);
Route::middleware('auth:sanctum')->post('orders/quick_order/response/{order_id}', [OrderController::class, 'QuickOrderResponse']);
//Route::middleware('auth:sanctum')->post('videos/{id}/views', [VideoController::class, 'incrementViewCount']);

Route::post('videos/{id}/views', [VideoController::class, 'incrementViewCount']);
Route::post('videos/{id}/tapped', [VideoController::class, 'incrementViewTappes']);
Route::get('videos/GetVideosByUserID/{user_id}', [VideoController::class, 'GetVideosByUserID']);

Route::middleware('auth:sanctum')->get('orders/GetOrderByMasterID/{id}', [OrderController::class, 'GetOrderByMasterID']);
Route::middleware('auth:sanctum')->get('orders/GetOrderByUserID/{id}', [OrderController::class, 'GetOrderByUserID']);
Route::middleware('auth:sanctum')->get('quick_orders/{quick_order_id}', [OrderController::class, 'show_quick_order']);
Route::middleware('auth:sanctum')->post('quick_orders/{quick_order_id}', [OrderController::class, 'store_quick_order']);

//Route::middleware('auth:sanctum')->post('/masters/{id}/click', [UserController::class, 'trackProfileClick']);

// ----------------------------------------------------------------------------------------------------

Route::middleware('auth:sanctum')->resource('users', UserController::class);
Route::middleware('auth:sanctum')->resource('payments', PaymentController::class);
Route::middleware('auth:sanctum')->resource('payment_statuses', PaymentStatusController::class);
Route::middleware('auth:sanctum')->resource('stores', StoreController::class);
Route::middleware('auth:sanctum')->resource('company_types', CompanyTypeController::class);
Route::middleware('auth:sanctum')->resource('roles', RoleController::class);

Route::resource('tags', TagController::class);
//Route::resource('videos', VideoController::class);

// Публичные маршруты
//Route::get('videos', [VideoController::class, 'index'])->middleware('optional.auth');
Route::get('videos', [VideoController::class, 'index']);
Route::get('videos/{video}', [VideoController::class, 'show']);
// Только для авторизованных
Route::middleware('auth:sanctum')->group(function () {
//    Route::get('videos', [VideoController::class, 'index']);
    Route::post('videos', [VideoController::class, 'store']);
    Route::put('videos/{video}', [VideoController::class, 'update']);
    Route::delete('videos/{video}', [VideoController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->resource('variables', VariableController::class);
Route::middleware('auth:sanctum')->resource('ratings', RatingController::class);
Route::middleware('auth:sanctum')->resource('order_statuses', OrderStatusController::class);
Route::middleware('auth:sanctum')->resource('order_reports', OrderReportController::class);

Route::middleware('auth:sanctum')->resource('orders', OrderController::class);
Route::middleware('auth:sanctum')->resource('favorites', FavoriteController::class);
Route::middleware('auth:sanctum')->resource('surveys', SurveyController::class);
Route::middleware('auth:sanctum')->resource('questions', QuestionController::class);
Route::middleware('auth:sanctum')->resource('options', OptionsController::class);
Route::middleware('auth:sanctum')->resource('question_types', QuestionTypesController::class);
Route::middleware('auth:sanctum')->resource('default_answers', DefaultAnswerController::class);
Route::middleware('auth:sanctum')->resource('user_answers', UserAnswerController::class);
Route::middleware('auth:sanctum')->resource('subscriptions', SubscriptionController::class);
Route::middleware('auth:sanctum')->resource('files', FileController::class);
Route::middleware('auth:sanctum')->resource('likes', LikeController::class);

Route::get('/countries', [CountryController::class, 'index']);
Route::get('/regions', [RegionController::class, 'index']);     // ?country_id=1
Route::get('/cities', [CityController::class, 'index']);