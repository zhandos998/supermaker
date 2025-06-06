<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoClick extends Model
{
    //
    use HasFactory;
    protected $fillable = ['video_id','user_id','ip','created_at'];

}
