<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnswer extends Model
{
    protected $fillable = ['question_id','user_survey_id','option_ids','custom_value','image_urls'];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'survey_id', 'user_survey_id');
    }

    public function options_2()
    {
        return $this->belongsToMany(
            Option::class,
            null, // Указываем null, так как связь идёт через JSON поле, а не через отдельную таблицу
            'id', // Локальный ключ из user_answers
            'id'  // Ключ из options
        )->whereIn('id', $this->option_ids ?? []);
    }

    protected $casts = [
        'option_ids' => 'array', // Автоматическое декодирование JSON
    ];
    protected $appends = ['options_data'];

    public function getOptionsDataAttribute()
    {
        if (empty($this->option_ids)) {
            return [];
        }

        $optionIds = json_decode($this->option_ids, true); // Декодируем JSON в массив

        if (empty($optionIds)) {
            return [];
        }

        return Option::whereIn('id', $optionIds)->get();
    }

//    public function options()
//    {
//        // Проверяем, есть ли option_ids
//         if (!empty($this->option_ids) && is_array($this->option_ids)) {
//             // Загружаем связанные Options через whereIn
//             return Option::whereIn('id', $this->option_ids)->get();
//         }
//         return Option::where('id', 0)->get();
////        return Option::whereIn('id', json_decode($this->option_ids));
//        // Если нет option_ids, возвращаем пустую коллекцию
//        // return collect();
//    }

    // public function options()
    // {
    //     // Проверяем, что option_ids не пустой массив
    //     if (!empty($this->option_ids) && is_array($this->option_ids)) {
    //         return Option::whereIn('id', $this->option_ids)->get();
    //     }
    //     return collect();
    //     // return $this->option_ids;
    //     // return $this->hasMany(Option::class, 'id', 'option_ids');
    //     // return $this->belongsTo(Option::class, 'option_ids')->whereIn('id', json_decode($this->option_ids, true) ?? []);
    //     // return Option::whereIn('id', $this->option_ids)->get();
    //     // return $this->hasMany(Option::class, 'question_id', 'question_id');
    // }

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }

    // public function survey()
    // {
    //     return $this->belongsTo(Survey::class);
    // }

    // public function video()
    // {
    //     return $this->belongsTo(Video::class);
    // }

    // Кастомный атрибут для options
    // public function getOptionsAttribute()
    // {
    //     $optionIds = json_decode($this->option_ids, true) ?: [];
    //     return Option::whereIn('id', $optionIds)->get();
    // }
}
