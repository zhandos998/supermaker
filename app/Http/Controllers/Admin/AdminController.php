<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('admin.home');
    }

    // public function view_feedbacks()
    // {
    //     $feedbacks = DB::table('feedbacks')
    //         ->get();
    //     return view('admin.feedbacks.feedbacks',['feedbacks'=>$feedbacks]);
    // }

    // public function view_feedback(Request $request, $id)
    // {
    //     if ($request->isMethod('post')){
    //         if ($request->status=="on"){
    //             $status=1;
    //         }
    //         else
    //         {
    //             $status=0;
    //         }
    //         DB::table('feedbacks')
    //             ->where('id',$id)
    //             ->update(['status'=>$status]);
    //         return redirect('admin/feedbacks');
    //     }
    //     $feedback = DB::table('feedbacks')
    //         ->where('id',$id)
    //         ->first();

    //     return view('admin.feedbacks.feedback',
    //         [
    //             'feedback'=>$feedback,
    //         ]);
    // }
}
