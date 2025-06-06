<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     protected $fillable = [
        'user_id', 'title', 'furniture_type', 'description', 'url', 'price', 'sizes',
        'is_fixed', 'views_count', 'preview_url', 'is_visible', 'tapped_count','in_stock'
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'videos_tags');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function subscriptions()
    {
        return $this->hasManyThrough(Subscription::class, User::class, 'master_id', 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'video_id');
    }


}
