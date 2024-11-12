<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultAnswer extends Model
{
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function master()
    {
        return $this->belongsTo(User::class, 'master_id');
    }
}
