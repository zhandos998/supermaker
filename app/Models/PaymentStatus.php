<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentStatus extends Model
{
    //
    use HasFactory;
    protected $fillable = ['name'];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'status_id');
    }
}
