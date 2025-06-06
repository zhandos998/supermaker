<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
    //
    protected $fillable = [
        'user_id',
        'amount',
        'status_id',
        'description',
        'check_url', // Не забудьте добавить это, если хотите обновлять URL чека
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(PaymentStatus::class, 'status_id');
    }

}

