<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    // Указываем, какие поля могут быть массово назначены

    protected $fillable = ['id','question_id', 'option_text', 'comment'];

    // Определение связи с вопросами
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // Связь с изображениями вариантов
    // public function images()
    // {
    //     return $this->hasMany(OptionImage::class);
    // }
}
