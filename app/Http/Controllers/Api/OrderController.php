<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Order; // Ensure you have an Order model
use App\Models\OrderStatus;
use App\Models\QuickOrder;
use App\Models\Variable;
use App\Models\User;
use App\Models\UserSurvey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function QuickOrderResponse(Request $request, $order_id)
    {
//        $validated = $request->validate([
//            'master_id' => 'required|exists:users,id',
//        ]);

        // Находим выбранный заказ
        $selectedOrder = Order::findOrFail($order_id);

        // Обновляем статус выбранного заказа на 14
        $selectedOrder->update([
//            'master_id' => $validated['master_id'],
            'status_id' => 14,
        ]);

        // Обновляем статус остальных заказов с таким же quick_order_id на 13
        Order::where('quick_order_id', $selectedOrder->quick_order_id)
            ->where('id', '!=', $selectedOrder->id) // Исключаем выбранный заказ
            ->update(['status_id' => 13]);

        return response()->json(['message' => 'Response submitted, order updated.']);
    }

    public function QuickOrder($user_survey_id = null)
    {
        // $validated = $request->validate([
        //     'user_id' => 'required|exists:users,id',
        // ]);
//        print($user_survey_id);
        // Создаем запись для быстрого заказа
        $quickOrder = QuickOrder::create([
            'user_id' => auth('sanctum')->id(),
            'group_iter' => 0,
            'refresh_time' => Carbon::now(),
            'responded' => false,
        ]);
        UserSurvey::where('id',$user_survey_id)
            ->update([
                'quick_order_id'=>$quickOrder->id,
            ]);


        // Отправляем заказы мастерам
        $this->SendQuickOrdersByUserId($quickOrder->user_id);


        return response()->json(['message' => 'Quick order created and sent to masters.']);
    }

    public function QuickOrdersUpdates()
    {
        $quick_order_iteration = Variable::where('id',8)->first()['value']; // 3 За раз отправлят к N мастерам заявку(ордер)
        // Находим активные быстрые заказы
        $quickOrders = QuickOrder::where('created_at', '<=', Carbon::now()->subHours(48))
            ->where('is_active',1)
            ->get();
        foreach ($quickOrders as $quickOrder) {
            $quickOrder->is_active = 0;
            $quickOrder->save();
        }

        Order::where('created_at', '<=', Carbon::now()->subHours(24))
            ->where('status_id',2)
            ->update(['status_id' => 3]);

        Order::where('created_at', '<=', Carbon::now()->subDays(14))
            ->where('status_id',3)
            ->delete();
    }

    public function GetOrderByMasterID($id)
    {
        $order = Order::where('master_id',$id)
            ->orderBy('updated_at', 'desc')
            ->get();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($order, Response::HTTP_OK);
    }

    public function GetOrderByUserID($id)
    {
        $order = Order::where('user_id',$id)
            ->orderBy('updated_at', 'desc')
            ->get();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($order, Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        $isMaster = $user->hasRole('master');
        $statusIds = explode(',', $request->query('status_id'));
        $mainStatus = $statusIds[0] ?? null;

        // Проверка корректности статусов
        foreach ($statusIds as $statusId) {
            if (!is_numeric($statusId) || !OrderStatus::find($statusId)) {
                return response()->json(['message' => 'Invalid status_id provided'], 400);
            }
        }

        // Подготовка основного запроса
        $orders = Order::query();

        if ($mainStatus) {
            $orders = match ((int)$mainStatus) {
                1, 4 => $orders->where('status_id', 1), // Новые / Отправленные
                2, 5 => $orders->where('status_id', 2), // Ответы / Отвеченные
                3, 6 => $orders->where('status_id', 3), // Архив
                default => $orders
            };
        }
        if ($request->filled('city_id')) {
            $orders = $orders->where('city_id', $request->city_id);
        }
        if ($request->filled('region_id')) {
            $orders = $orders->where('region_id', $request->region_id);
        }
        if ($request->filled('country_id')) {
            $orders = $orders->where('country_id', $request->country_id);
        }

        $orders = $orders
            ->where($isMaster ? 'master_id' : 'user_id', $user->id)
            ->with('user_surveys.user_answers.question')
            ->orderBy('quick_order_id')
            ->orderByDesc('created_at')
            ->get();

        // Обработка дополнительных данных заказов
        $orders->each(function ($order) {
            if (
                $order->relationLoaded('user_surveys') &&
                $order->user_surveys
            ) {
                foreach ($order->user_surveys->user_answers ?? [] as $answer) {
                    $answer->options = Option::whereIn(
                        'id',
                        json_decode($answer->option_ids ?? '[]')
                    )->get();
                }
            }

            $order->is_under_video = $order->video_id !== null;
            $order->title = optional(
                $order->user_surveys?->user_answers->first()?->options[0]
            )->option_text;
        });

        // Если нужны быстрые заказы — добавим
        if ($isMaster && in_array((int)$mainStatus, [1, 4])) {
            $quickOrdersQuery = QuickOrder::where('is_active', 1)
                ->with('user_surveys.user_answers.question')
                ->orderByDesc('created_at');

            if ($request->filled('city_id')) {
                $quickOrdersQuery->where('city_id', $request->city_id);
            }
            if ($request->filled('region_id')) {
                $quickOrdersQuery->where('region_id', $request->region_id);
            }
            if ($request->filled('country_id')) {
                $quickOrdersQuery->where('country_id', $request->country_id);
            }

            // Фильтрация для мастера: только те, на которые он ещё не ответил
            if ((int)$mainStatus === 1) {
                $quickOrdersQuery->whereNotExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('orders')
                        ->whereColumn('orders.quick_order_id', 'quick_orders.id')
                        ->where('orders.master_id', $user->id);
                });
            }

            $quickOrders = $quickOrdersQuery->get();

            $quickOrders->each(function ($quick) {
                $quick->video_id = null;
                $quick->status_id = 9;
                $quick->quick_order_id = $quick->id;
                $quick->master_id = 2;
                $quick->master_price = null;
                $quick->master_time = null;
                $quick->master_comment = null;
                $quick->is_read = 0;
                $quick->user_survey_id = $quick->user_surveys[0]->id ?? null;
                $quick->is_quick_order = true;
                $quick->is_under_video = false;
                $quick->title = optional($quick->user_surveys->first()?->user_answers->first()?->options_data[0] ?? null)->option_text;
            });

            $orders = $orders->merge($quickOrders);
        }

        return response()->json($orders, Response::HTTP_OK);
    }

    public function show($id)
    {
        $order = Order::with([
            'user_surveys.user_answers.question',
        ])->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [];

        foreach ($order->user_surveys->user_answers as $answer) {
            $options = collect($answer->options_data)->pluck('option_text')->toArray(); // Собираем все option_text в массив


            $optionString = implode(',', $options);

            $imageUrls = is_string($answer->image_urls)
                ? json_decode($answer->image_urls, true)
                : $answer->image_urls;

            // Пропускаем, если всё пусто
            if (empty($optionString) && $answer->custom_value === null && empty($imageUrls)) {
                continue;
            }

            $data[] = [
                'answer' => [
                    'question' => $answer->question->topic,
                    'options' => implode(',', $options), // Объединяем через запятую
                    'custom_value' => $answer->custom_value,
                    'image_urls' => is_string($answer->image_urls)
                        ? json_decode($answer->image_urls, true)
                        : $answer->image_urls,
//                    'comment' => $answer->comment,
//                    'comment_image' => $answer->comment_image,
                ]
            ];
        }
        //        foreach ($order->user_surveys->user_answers as $answer) {
        //            echo $answer->question->title; // например, текст вопроса
        //
        //            foreach ($answer->options_data as $option) {
        //                echo $option->title; // например, текст варианта ответа
        //            }
        //        }

        $comment = null;
        $comment_image = null;
        if ($data[0]['answer']['options'] == 'Кухня' && $data[0]['answer']['question'] == 'Тип мебели'){
            if ($data[1]['answer']['options'] == 'Линейный (прямой)' && $data[1]['answer']['question'] == 'Форма кухни'){
                $comment = "Линейный (прямой)";
                $comment_image = "https://mebelplace.kz/storage/files/TJLcmWNCQprkpiXoB8dw75o6pqTh5MXKcCfe3US2.jpg";
            }
            else if ($data[1]['answer']['options'] == 'Угловая (Г-образная) кухня' && $data[1]['answer']['question'] == 'Форма кухни'){
                $comment = "Угловая (Г-образная) кухня";
                $comment_image = "https://mebelplace.kz/storage/files/ZdmDdAl9WS9jyKPvTXJ0ubiIVSKanSCiE8ARdUVX.jpg";
            }
            else if ($data[1]['answer']['options'] == 'П-образная (U-образная)' && $data[1]['answer']['question'] == 'Форма кухни'){
                $comment = "П-образная (U-образная)";
                $comment_image = "https://mebelplace.kz/storage/files/TGGnFsv8uvw9N9SwjzMoc0DqEXkuv1XxsIk1Jaju.jpg";
            }
            else if ($data[1]['answer']['options'] == 'Параллельная (двухрядная) кухня' && $data[1]['answer']['question'] == 'Форма кухни'){
                $comment_image = "https://mebelplace.kz/storage/files/SNFGkqvXhzdYxZ5P8JhzPtvFfC9OjLexv95Lv97G.jpg";
                $comment = "Параллельная (двухрядная) кухня";
            }
            else if ($data[1]['answer']['options'] == 'Полуостровная кухня' && $data[1]['answer']['question'] == 'Форма кухни'){
                $comment_image = "https://mebelplace.kz/storage/files/N9uORLce0bMJlwkSP0Fsh8WCEfKYMlDyCa2ZtAFg.jpg";
                $comment = "Полуостровная кухня";
            }
        }
        else
        if ($data[0]['answer']['options'] == 'Шкаф' && $data[0]['answer']['question'] == 'Тип мебели'){
                $comment_image = "https://mebelplace.kz/storage/files/3zZXVS0hUumyrFZrAqG0T8VoZwhRqINdSf7295sj.jpg";
                $comment = "Шкаф";
            }


//        dd($order);
        // Теперь вернем отформатированный ответ



        return response()->json([
            'id' => $order->id,
            'user_survey_id' => $order->user_survey_id,
            'user_id' => $order->user_id,
            'master_id' => $order->master_id,
            'video_id' => $order->video_id,
            'master_price' => $order->master_price,
            'master_time' => $order->master_time,
            'master_comment' => $order->master_comment,
            'status_id' => $order->status_id,
            'quick_order_id' => $order->quick_order_id,
            'is_quick_order' => $order->quick_order_id !== null,
            'is_read' => $order->is_read,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'comment' => $comment,
            'comment_image' => $comment_image,
            'phone' => ($order->status_id == 2 || $order->status_id == 3) ? $order->master->phone ?? null : null,
            'data' => $data,
        ], Response::HTTP_OK);

//        return response()->json($order, Response::HTTP_OK);
    }

    public function show_quick_order($id)
    {
        $order = QuickOrder::with([
            'user_surveys.user_answers.question',
        ])->find($id);

        if (!$order) {
            return response()->json(['message' => 'QuickOrder not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [];

//        return $order->user_surveys;
        foreach ($order->user_surveys[0]->user_answers as $answer) {
            $options = collect($answer->options_data)->pluck('option_text')->toArray(); // Собираем все option_text в массив

            $optionString = implode(',', $options);

            $imageUrls = is_string($answer->image_urls)
                ? json_decode($answer->image_urls, true)
                : $answer->image_urls;

            // Пропускаем, если всё пусто
            if (empty($optionString) && $answer->custom_value === null && empty($imageUrls)) {
                continue;
            }

            $data[] = [
                'answer' => [
                    'question' => $answer->question->topic,
//                    'question' => $answer->question->text,
                    'options' => implode(',', $options), // Объединяем через запятую
                    'custom_value' => $answer->custom_value,
                    'image_urls' => is_string($answer->image_urls)
                        ? json_decode($answer->image_urls, true)
                        : $answer->image_urls,
                ]
            ];
        }
        $comment = null;
        $comment_image = null;
//        print($data[0]['answer']['options']);
//        print('-------------------------------');
//        print($data[0]['answer']['question']);
//        print('-------------------------------');
//        print($data[1]['answer']['options']);
//        print('-------------------------------');
//        print($data[1]['answer']['question']);
//        print('-------------------------------');
        if ($data[0]['answer']['options'] == 'Кухня' && $data[0]['answer']['question'] == 'Тип мебели'){
            if ($data[1]['answer']['options'] == 'Линейный (прямой)' && $data[1]['answer']['question'] == 'Форма кухни'){
                $comment = "Линейный (прямой)";
                $comment_image = "https://mebelplace.kz/storage/files/TJLcmWNCQprkpiXoB8dw75o6pqTh5MXKcCfe3US2.jpg";
            }
            else if ($data[1]['answer']['options'] == 'Угловая (Г-образная) кухня' && $data[1]['answer']['question'] == 'Форма кухни'){
                $comment = "Угловая (Г-образная) кухня";
                $comment_image = "https://mebelplace.kz/storage/files/ZdmDdAl9WS9jyKPvTXJ0ubiIVSKanSCiE8ARdUVX.jpg";
            }
            else if ($data[1]['answer']['options'] == 'П-образная (U-образная)' && $data[1]['answer']['question'] == 'Форма кухни'){
                $comment = "П-образная (U-образная)";
                $comment_image = "https://mebelplace.kz/storage/files/TGGnFsv8uvw9N9SwjzMoc0DqEXkuv1XxsIk1Jaju.jpg";
            }
            else if ($data[1]['answer']['options'] == 'Параллельная (двухрядная) кухня' && $data[1]['answer']['question'] == 'Форма кухни'){
                $comment_image = "https://mebelplace.kz/storage/files/SNFGkqvXhzdYxZ5P8JhzPtvFfC9OjLexv95Lv97G.jpg";
                $comment = "Параллельная (двухрядная) кухня";
            }
            else if ($data[1]['answer']['options'] == 'Полуостровная кухня' && $data[1]['answer']['question'] == 'Форма кухни'){
                $comment_image = "https://mebelplace.kz/storage/files/N9uORLce0bMJlwkSP0Fsh8WCEfKYMlDyCa2ZtAFg.jpg";
                $comment = "Полуостровная кухня";
            }
        }
        else if ($data[0]['answer']['options'] == 'Шкаф' && $data[0]['answer']['question'] == 'Тип мебели'){
            $comment_image = "https://mebelplace.kz/storage/files/3zZXVS0hUumyrFZrAqG0T8VoZwhRqINdSf7295sj.jpg";
            $comment = "Шкаф";
        }

        //        foreach ($order->user_surveys->user_answers as $answer) {
        //            echo $answer->question->title; // например, текст вопроса
        //
        //            foreach ($answer->options_data as $option) {
        //                echo $option->title; // например, текст варианта ответа
        //            }
        //        }

//        dd($order);
        // Теперь вернем отформатированный ответ
        return response()->json([
            'id' => $order->id,
            'user_survey_id' => $order->user_survey_id,
            'user_id' => $order->user_id,
            'master_id' => $order->master_id,
            'video_id' => $order->video_id,
            'master_price' => $order->master_price,
            'master_time' => $order->master_time,
            'master_comment' => $order->master_comment,
            'status_id' => 1,
            'quick_order_id' => $order->id,
            'is_quick_order' => true,
            'is_read' => 1,
            'comment' => $comment,
            'comment_image' => $comment_image,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'data' => $data,
        ], Response::HTTP_OK);

//        return response()->json($order, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        // return $request->all();
        $validatedData = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'user_survey_id' => 'required|integer|exists:user_surveys,id',
            'master_id' => 'required|integer|exists:users,id',
            'video_id' => 'required|integer|exists:videos,id',
            'master_price' => 'required|string|min:0',
            'master_time' => 'required|string|min:1',
            'city_id' => 'nullable|integer|exists:cities,id',
            'region_id' => 'nullable|integer|exists:regions,id',
            'country_id' => 'nullable|integer|exists:countries,id',
//            'status_id' => 'required|integer|exists:order_statuses,id',
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $data = $validatedData->validated();

        // Устанавливаем статус вручную (например, 1 — "новый")
        $data['status_id'] = 1;

        $order = Order::create($data);

        return response()->json($order, Response::HTTP_CREATED);
    }

    public function store_quick_order(Request $request,$quick_order_id)
    {
//         return $request->all();
        // Валидация тела запроса
        $validatedData = Validator::make($request->all(), [
            'master_price' => 'required|integer',
            'master_time' => 'required|string',
            'master_comment' => 'nullable|string',
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        // Отдельная валидация параметра из URL
//        $checkQuickOrder = Validator::make(
//            ['quick_order_id' => $quick_order_id],
//            ['quick_order_id' => 'required|integer|exists:quick_orders,id']
//        );


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }
//        return QuickOrder::find($request->quick_order_id);
        $quick_order = QuickOrder::find($quick_order_id);

        $master = auth('sanctum')->user(); // убедись что есть связь master()

        $requiredAmount = Variable::where('id',13)->first()['value']; // например, сколько нужно списать

        if ($master->wallet < $requiredAmount) {
            return response()->json(['message' => 'Недостаточно средств для ответа.'], 200);
        }

        // Списываем деньги
        $master->wallet -= $requiredAmount;
        $master->save();

        $order = Order::create(
            [
                'user_survey_id' => $quick_order->user_survey_id,
                'user_id' => $quick_order->user_id,
                'master_id' => auth('sanctum')->id(),
                'master_price' => $request->master_price,
                'master_time' => $request->master_time,
                'master_comment' => $request->master_comment ?? null,
                'status_id' => 2,
                'quick_order_id' => $quick_order_id,
            ]
        );
        $quick_order->masters_left -= 1;
        $quick_order->save();
        if ($quick_order->masters_left == 0){
            $quick_order->delete();
        }
        return response()->json($order, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
//            'status_id' => 'sometimes|integer|exists:order_statuses,id',
            'status' => 'required|integer',
            'master_price' => 'sometimes|nullable|integer|min:0',
            'master_time' => 'sometimes|nullable|string',
//            'video_id' => 'sometimes|integer|exists:videos,id',
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $data = $validatedData->validated();
        // Если статус сменился на 4 — проверка баланса

        if (isset($data['status'])) {
            $current = $order->status_id;
            $status = $data['status'];

            // Пример переходов (можешь вынести в конфиг или таблицу)
            $status_true = [
                1 => 2,
//                2 => 4,
//                4 => 6,
//                6 => 7,
            ];
            $status_false = [
                1 => 3,
//                2 => 4,
//                4 => 6,
//                6 => 7,
            ];

            if($status == 1){
                if ($order->status_id == 1) {
                    $master = $order->master; // убедись что есть связь master()

                    $requiredAmount = Variable::where('id',13)->first()['value']; // например, сколько нужно списать

//                    print($master->wallet);
//                    print($requiredAmount);
                    if ($master->wallet < $requiredAmount) {
                        return response()->json(['message' => 'Недостаточно средств для принятия заказа'], 200);
                    }

                    // Списываем деньги
                    $master->wallet -= $requiredAmount;
                    $master->save();
                }

                if (!isset($status_true[$current])) {
                    return response()->json($order, Response::HTTP_OK);
                }
                $data['status_id'] = $status_true[$current];
            }else
            if($status == 0){

                if (!isset($status_false[$current])) {
                    return response()->json($order, Response::HTTP_OK);
                }
                $data['status_id'] = $status_false[$current];
            }
//            if (isset($statusFlow[$current])) {
//                $data['status_id'] = $statusFlow[$current];
//            }
        }

        // Обновляем только те поля, которые были переданы в запросе
        $updateData = [];

        if (array_key_exists('master_price', $data)) {
            $updateData['master_price'] = $data['master_price'];
        }
        if (array_key_exists('master_time', $data)) {
            $updateData['master_time'] = $data['master_time'];
        }
        if (array_key_exists('status_id', $data)) {
            $updateData['status_id'] = $data['status_id'];
        }

        $order->update($updateData);

        return response()->json($order, Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $order->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function destroy_quick_order($id)
    {
        $order = QuickOrder::find($id);

        if (!$order) {
            return response()->json(['message' => 'quick_orders not found'], Response::HTTP_NOT_FOUND);
        }

        $order->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
