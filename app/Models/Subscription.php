<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'master_id'];

    // Отношение с пользователем
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Отношение с мастером
    public function master()
    {
        return $this->belongsTo(User::class, 'master_id');
    }
}
