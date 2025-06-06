<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnswerImage extends Model
{
    protected $fillable = ['path'];

    public $timestamps = false;
    protected $table = 'user_answers_images';
//    public function question()
//    {
//        return $this->belongsTo(Question::class, 'question_id', 'id');
//    }
}
