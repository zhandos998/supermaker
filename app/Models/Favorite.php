<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'video_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Отношение с мастером
    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id');
    }
}
