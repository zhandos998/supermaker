<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
// use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentStatusController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\VariableController;
use App\Http\Controllers\Admin\RatingController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Admin\OrderReportController;
use App\Http\Controllers\Admin\FavoriteController;
use App\Http\Controllers\Admin\SurveyController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\DefaultAnswerController;
use App\Http\Controllers\Admin\OptionsController;
use App\Http\Controllers\Admin\QuestionTypesController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UserAnswerController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\LikeController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/admin', [AdminController::class, 'index'])->name('admin_index');
Route::post('/admin/users/{user}/topup', [UserController::class, 'topup'])->name('admin.users.topup');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('payment_statuses', PaymentStatusController::class);
    Route::resource('videos', VideoController::class);
    Route::resource('cities', CityController::class);
    Route::resource('countries', CountryController::class);
//    Route::resource('order_reports', OrderReportController::class);
//    Route::resource('order_statuses', OrderStatusController::class);
//    Route::resource('files', FileController::class);
//    Route::resource('ratings', RatingController::class);
//    Route::resource('roles', RoleController::class);
//    Route::resource('stores', StoreController::class);
//    Route::resource('surveys', SurveyController::class);
//    Route::resource('tags', TagController::class);
    Route::resource('variables', VariableController::class);
});

// Route::post('/api/verify-account', [AuthController::class, 'verifyAccount']);


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/admin/logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
//    ->middleware(['auth', 'admin']);

Route::view('/policy', 'policy')->name('policy');


//Route::middleware(['auth:sanctum'])->group(function () {
//    Route::get('/api/documentation', function () {
//        return view('l5-swagger::index');
//    });
//});