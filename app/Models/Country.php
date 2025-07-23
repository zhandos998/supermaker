<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'code'];
    protected $hidden = ['created_at', 'updated_at'];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}
