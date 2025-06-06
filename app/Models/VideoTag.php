<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoTag extends Model
{
    use HasFactory;

    // protected $table = 'video_tag';  // Указываем имя таблицы, если оно отличается от множественного числа модели

    protected $fillable = [
        'video_id',
        'tag_id',
    ];

    // Определение связи с моделью Video
    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    // Определение связи с моделью Tag
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
