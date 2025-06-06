<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReport extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'order_id',
        'photo_urls',
        'description',
    ];

    protected $casts = [
        'photo_urls' => 'array', // Автоматическая работа с массивом JSON
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
