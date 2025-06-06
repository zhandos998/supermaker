<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultSurvey extends Model
{
    protected $fillable = ['id','survey_id','video_id','master_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'default_survey_id');
    }

    public function default_answers()
    {
        return $this->hasMany(DefaultAnswer::class, 'default_survey_id', 'id');
    }

    // Кастомный атрибут для options
    // public function getOptionsAttribute()
    // {
    //     $optionIds = json_decode($this->option_ids, true) ?: [];
    //     return Option::whereIn('id', $optionIds)->get();
    // }

    // public function options()
    // {
    //     return Option::whereIn('id', json_decode($this->option_ids, true))->get();
    //     // return $this->hasMany(Option::class, 'question_id', 'question_id');
    // }
}
