<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['id','survey_id', 'text', 'type_id', 'comment'];

    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id', 'id');
    }

    public function defaultAnswers()
    {
        return $this->hasMany(DefaultAnswer::class);
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
    // Связь с опциями

    public function options()
    {
        return $this->hasMany(Option::class, 'question_id', 'id');
    }
}
