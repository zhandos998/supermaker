<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function defaultAnswers()
    {
        return $this->hasMany(DefaultAnswer::class);
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
}
