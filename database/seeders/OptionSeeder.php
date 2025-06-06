<?php

namespace Database\Seeders;

use App\Models\Option;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
//        $items =
//        [
//            // 1 1 Выберите мебель на заказ
//            ['question_id' => 1, 'option_text' => "Кухня"],
//            ['question_id' => 1, 'option_text' => "Шкаф"],
//            ['question_id' => 1, 'option_text' => "Другое"],
//            // 2 1 Выберите тип кухни
//            ['question_id' => 2, 'option_text' => "Линейный( прямой )"],
//            ['question_id' => 2, 'option_text' => "Угловая ( Г-образная ) кухня"],
//            ['question_id' => 2, 'option_text' => "П-образная (U-образная)"],
//            ['question_id' => 2, 'option_text' => "Параллельная (двухрядная) кухня"],
//            ['question_id' => 2, 'option_text' => "Полуостровная кухня"],
//            ['question_id' => 2, 'option_text' => "Островная кухня"],
//            // 3 1 Выберите фасад кухни
//            ['question_id' => 3, 'option_text' => "ЛДСП"],
//            ['question_id' => 3, 'option_text' => "МДФ крашенный"],
//            ['question_id' => 3, 'option_text' => "МДФ с ПВХ-плёнкой"],
//            ['question_id' => 3, 'option_text' => "Акриловые кухни"],
//            ['question_id' => 3, 'option_text' => "Шпон"],
//            // 4 1 Введите размер кухонной гарнитуры Высота
//            // 5 1 Введите размер кухонной гарнитуры Ширина
//            // 6 1 Высота навесных шкафов
//            ['question_id' => 6, 'option_text' => "Стандарт 70 - 90см"],
//            ['question_id' => 6, 'option_text' => "Высокие 90 - 110 см"],
//            ['question_id' => 6, 'option_text' => "До потолка"],
//            // 7 1 Будет ли нужен фартук?
//            ['question_id' => 7, 'option_text' => "Да"],
//            ['question_id' => 7, 'option_text' => "Нет"],
//            // 8 1 Какая будет мойка?
//            ['question_id' => 8, 'option_text' => "Своя"],
//            ['question_id' => 8, 'option_text' => "От фирмы"],
//            // 9 1 Какая будет антрисоли?
//            ['question_id' => 9, 'option_text' => "Антресоли выпирающие "],
//            ['question_id' => 9, 'option_text' => "Антресоли вровень"],
//            // 10 1 Выберите бренд фурнитуры
//            ['question_id' => 10, 'option_text' => "Blum"],
//            ['question_id' => 10, 'option_text' => "Boyard"],
//            ['question_id' => 10, 'option_text' => "Hafele"],
//            ['question_id' => 10, 'option_text' => "EN7"],
//            // 11 Выберите технику, которую будете выстраивать в кух...
//            ['question_id' => 11, 'option_text' => "Холодильник встраиваемый в шкаф"],
//            ['question_id' => 11, 'option_text' => "Холодильник отдельно стоящий"],
//            ['question_id' => 11, 'option_text' => "Плита варочная поверхность"],
//            ['question_id' => 11, 'option_text' => "Плита отдельно стоящая"],
//            ['question_id' => 11, 'option_text' => "Духовка под столешницу"],
//            ['question_id' => 11, 'option_text' => "Духовка в пенал"],
//            ['question_id' => 11, 'option_text' => "Вытяжка"],
//            ['question_id' => 11, 'option_text' => "Посудомоечная машина отдельно стоящая"],
//            // 12 Отметьте свойства потолка
//            ['question_id' => 12, 'option_text' => "Ровный потолок"],
//            ['question_id' => 12, 'option_text' => "Мешает ригель"],
//            ['question_id' => 12, 'option_text' => "Перепад потолка"],
//            // 13 Выберите дополнительные элементы
//            ['question_id' => 13, 'option_text' => "Фасад стекло"],
//            ['question_id' => 13, 'option_text' => "Подсветка"],
//            ['question_id' => 13, 'option_text' => "Ленты"],
//
//            ['question_id' => 14, 'option_text' => "Сейчас"],
//            ['question_id' => 14, 'option_text' => "В течение 2-х недель"],
//            ['question_id' => 14, 'option_text' => "В течение месяца"],
//            ['question_id' => 14, 'option_text' => "От 1-го до 3-х месяцев"],
//
//            ['question_id' => 15, 'option_text' => "Да"],
//            ['question_id' => 15, 'option_text' => "Нет"],
//
//        ];
        $items =
            [
                [
                    "id" => 2,
                    "question_id" => 1,
                    "option_text" => "Кухня"
                ],
                [
                    "id" => 3,
                    "question_id" => 1,
                    "option_text" => "Шкаф"
                ],
                [
                    "id" => 4,
                    "question_id" => 1,
                    "option_text" => "Другое"
                ],
                [
                    "id" => 5,
                    "question_id" => 2,
                    "option_text" => "Линейный (прямой)"
                ],
                [
                    "id" => 6,
                    "question_id" => 2,
                    "option_text" => "Угловая (Г-образная) кухня"
                ],
                [
                    "id" => 7,
                    "question_id" => 2,
                    "option_text" => "П-образная (U-образная)"
                ],
                [
                    "id" => 8,
                    "question_id" => 2,
                    "option_text" => "Параллельная (двухрядная) кухня"
                ],
                [
                    "id" => 9,
                    "question_id" => 2,
                    "option_text" => "Полуостровная кухня"
                ],
                [
                    "id" => 10,
                    "question_id" => 2,
                    "option_text" => "Островная кухня"
                ],
                [
                    "id" => 11,
                    "question_id" => 3,
                    "option_text" => "ЛДСП"
                ],
                [
                    "id" => 12,
                    "question_id" => 3,
                    "option_text" => "МДФ крашенный"
                ],
                [
                    "id" => 13,
                    "question_id" => 3,
                    "option_text" => "МДФ с ПВХ-плёнкой"
                ],
                [
                    "id" => 14,
                    "question_id" => 3,
                    "option_text" => "Акриловые кухни"
                ],
                [
                    "id" => 15,
                    "question_id" => 3,
                    "option_text" => "Шпон"
                ],
                [
                    "id" => 16,
                    "question_id" => 7,
                    "option_text" => "Стандарт 70 - 90см"
                ],
                [
                    "id" => 17,
                    "question_id" => 7,
                    "option_text" => "Высокие 90 - 110 см"
                ],
                [
                    "id" => 18,
                    "question_id" => 7,
                    "option_text" => "До потолка"
                ],
                [
                    "id" => 19,
                    "question_id" => 8,
                    "option_text" => "Да"
                ],
                [
                    "id" => 20,
                    "question_id" => 8,
                    "option_text" => "Нет"
                ],
                [
                    "id" => 21,
                    "question_id" => 9,
                    "option_text" => "Своя"
                ],
                [
                    "id" => 22,
                    "question_id" => 9,
                    "option_text" => "От фирмы"
                ],
                [
                    "id" => 23,
                    "question_id" => 10,
                    "option_text" => "Антресоли выпирающие"
                ],
                [
                    "id" => 24,
                    "question_id" => 10,
                    "option_text" => "Антресоли вровень"
                ],
                [
                    "id" => 25,
                    "question_id" => 11,
                    "option_text" => "Blum"
                ],
                [
                    "id" => 26,
                    "question_id" => 11,
                    "option_text" => "Boyard"
                ],
                [
                    "id" => 27,
                    "question_id" => 11,
                    "option_text" => "Hafele"
                ],
                [
                    "id" => 28,
                    "question_id" => 11,
                    "option_text" => "EN7"
                ],
                [
                    "id" => 29,
                    "question_id" => 12,
                    "option_text" => "Холодильник встраиваемый в шкаф"
                ],
                [
                    "id" => 30,
                    "question_id" => 12,
                    "option_text" => "Холодильник отдельно стоящий"
                ],
                [
                    "id" => 31,
                    "question_id" => 12,
                    "option_text" => "Плита варочная поверхность"
                ],
                [
                    "id" => 32,
                    "question_id" => 12,
                    "option_text" => "Плита отдельно стоящая"
                ],
                [
                    "id" => 33,
                    "question_id" => 12,
                    "option_text" => "Духовка под столешницу"
                ],
                [
                    "id" => 34,
                    "question_id" => 12,
                    "option_text" => "Духовка в пенал"
                ],
                [
                    "id" => 35,
                    "question_id" => 12,
                    "option_text" => "Вытяжка"
                ],
                [
                    "id" => 36,
                    "question_id" => 12,
                    "option_text" => "Посудомоечная машина отдельно стоящая"
                ],
                [
                    "id" => 37,
                    "question_id" => 13,
                    "option_text" => "Ровный потолок"
                ],
                [
                    "id" => 38,
                    "question_id" => 13,
                    "option_text" => "Мешает ригель"
                ],
                [
                    "id" => 39,
                    "question_id" => 13,
                    "option_text" => "Перепад потолка"
                ],
                [
                    "id" => 40,
                    "question_id" => 14,
                    "option_text" => "Фасад стекло"
                ],
                [
                    "id" => 41,
                    "question_id" => 14,
                    "option_text" => "Подсветка"
                ],
                [
                    "id" => 42,
                    "question_id" => 14,
                    "option_text" => "Ленты"
                ],
                [
                    "id" => 43,
                    "question_id" => 15,
                    "option_text" => "Сейчас"
                ],
                [
                    "id" => 44,
                    "question_id" => 15,
                    "option_text" => "В течение 2-х недель"
                ],
                [
                    "id" => 45,
                    "question_id" => 15,
                    "option_text" => "В течение месяца"
                ],
                [
                    "id" => 46,
                    "question_id" => 15,
                    "option_text" => "От 1-го до 3-х месяцев"
                ],
                [
                    "id" => 47,
                    "question_id" => 16,
                    "option_text" => "Да"
                ],
                [
                    "id" => 48,
                    "question_id" => 16,
                    "option_text" => "Нет"
                ],
                [
                    "id" => 49,
                    "question_id" => 20,
                    "option_text" => "Шкаф - распашные двери"
                ],
                [
                    "id" => 50,
                    "question_id" => 20,
                    "option_text" => "Шкаф - купе"
                ],
                [
                    "id" => 51,
                    "question_id" => 20,
                    "option_text" => "Шкаф - прихожая"
                ]
            ];

        foreach ($items as $item) {
            Option::create($item);
        }
//        php artisan db:seed --class=OptionSeeder
    }
}
