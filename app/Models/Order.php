<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

//    protected $fillable = ['user_id', 'user_survey_id', 'video_id', 'master_id', 'master_price', 'master_time', 'status_id','quick_order_id'];
    protected $guarded = [];

    public function reports()
    {
        return $this->hasMany(OrderReport::class);
    }

    public function user_surveys()
    {
        return $this->belongsTo(UserSurvey::class, 'user_survey_id', 'id');
    }

    public function user_answers()
    {
        return $this->hasMany(UserAnswer::class, 'survey_id', 'user_survey_id');
    }
    public function master()
    {
        return $this->belongsTo(User::class, 'master_id');
    }
}
