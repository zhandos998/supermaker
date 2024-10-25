<?php

use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Route::get('/send-mail', function () {
//     $details = [
//         'title' => 'Тестовое письмо',
//         'body' => 'Это тестовое письмо, отправленное через Mail.ru в Laravel.'
//     ];

//     Mail::to('zhandos998@gmail.com')->send(new \App\Mail\TestMail($details));
//     // dd($details);
//     return 'Письмо отправлено!';
// });

