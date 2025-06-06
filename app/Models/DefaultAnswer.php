<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultAnswer extends Model
{

    protected $fillable = ['default_survey_id', 'question_id','option_ids','custom_value'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function master()
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

    // Кастомный атрибут для options
    // public function getOptionsAttribute()
    // {
    //     $optionIds = json_decode($this->option_ids, true) ?: [];
    //     return Option::whereIn('id', $optionIds)->get();
    // }

    public function options()
    {
        return Option::whereIn('id', json_decode($this->option_ids, true))->get();
        // return $this->hasMany(Option::class, 'question_id', 'question_id');
    }
}
