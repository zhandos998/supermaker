<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoView extends Model
{
    use HasFactory;

    // protected $table = 'video_tag';  // Указываем имя таблицы, если оно отличается от множественного числа модели

    protected $fillable = [
        'video_id',
        'user_id',
        'ip',
        'created_at'
    ];

    // Определение связи с моделью Video
    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
