<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Связь с пользователем.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function user_surveys()
    {
        return $this->hasMany(UserSurvey::class, 'quick_order_id', 'id');
//        return $this->hasMany(UserSurvey::class, 'quick_order_id', 'id');
    }
}
