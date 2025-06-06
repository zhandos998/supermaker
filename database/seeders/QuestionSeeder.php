<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $items = [
            ['survey_id' => 1, 'text' => "Выберите мебель на заказ" ,'type_id' => 1],
            ['survey_id' => 1, 'text' => "Выберите тип кухни" ,'type_id' => 1 ],
            ['survey_id' => 1, 'text' => "Выберите фасад кухни" ,'type_id' => 3 ],
            ['survey_id' => 1, 'text' => "Введите размер кухонной гарнитуры Высота" ,'type_id' => 5 ],
            ['survey_id' => 1, 'text' => "Введите размер кухонной гарнитуры Ширина" ,'type_id' => 5 ],
            ['survey_id' => 1, 'text' => "Высота навесных шкафов" ,'type_id' => 1 ],
            ['survey_id' => 1, 'text' => "Будет ли нужен фартук?" ,'type_id' => 1 ],
            ['survey_id' => 1, 'text' => "Какая будет мойка?" ,'type_id' => 1 ],
            ['survey_id' => 1, 'text' => "Какая будет антрисоли?" ,'type_id' => 1 ],
            ['survey_id' => 1, 'text' => "Выберите бренд фурнитуры" ,'type_id' => 3 ],
            ['survey_id' => 1, 'text' => "Выберите технику, которую будете выстраивать в кухонный гарнитур" ,'type_id' => 2 ],
            ['survey_id' => 1, 'text' => "Отметьте свойства потолка" ,'type_id' => 4 ],
            ['survey_id' => 1, 'text' => "Выберите дополнительные варианты" ,'type_id' => 2 ],
            ['survey_id' => 1, 'text' => "Сроки установки кухни" ,'type_id' => 1 ],
            ['survey_id' => 1, 'text' => "Будет ли нужна рассрочка?" ,'type_id' => 1 ],
            ['survey_id' => 1, 'text' => "Введите адрес установки и доставки" ,'type_id' => 5 ],
            ['survey_id' => 1, 'text' => "Добавьте изображения где будет установлена мебель" ,'type_id' => 6 ],
            ['survey_id' => 1, 'text' => "Добавьте изображения эскиз" ,'type_id' => 6 ],

        ];

        foreach ($items as $item) {
            Question::create($item);
        }
    }
}
