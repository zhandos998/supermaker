<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use http\Env\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Pluralizer;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check() && Auth::user()->hasRole('admin')) {
                // Перенаправление, если пользователь — админ
                return redirect('/admin');
            }

            return $next($request);
        });
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // dd(Auth::user());
        // Проверяем, является ли пользователь администратором
        // if (Auth::user() && Auth::user()->role === 'admin') {
        //     // Логика для админа
        //     return redirect('/admin');
        // }
        return view('home');
    }
}
