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
    /**
     * @OA\Post(
     *     path="/api/orders/quick_order/response/{order_id}",
     *     tags={"Quick Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Respond to a quick order",
     *     description="Updates the selected order status to 14 and others with the same quick_order_id to 13.",
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="ID of the selected order",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response submitted, order updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Response submitted, order updated.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/orders/quick_order/{user_survey_id}",
     *     tags={"Quick Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Create a quick order",
     *     description="Creates a quick order and assigns it to masters.",
     *     @OA\Parameter(
     *         name="user_survey_id",
     *         in="path",
     *         required=false,
     *         description="Optional survey ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quick order created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Quick order created and sent to masters.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
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
        $quickOrders = QuickOrder::where('created_at', '<=', Carbon::now()->subHours(24))
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

    /**
     * @OA\Get(
     *     path="/api/orders/GetOrderByMasterID/{id}",
     *     tags={"Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Get an order by master ID",
     *     description="Returns a single order",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order found"
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/orders/GetOrderByUserID/{id}",
     *     tags={"Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Get an order by master ID",
     *     description="Returns a single order",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order found"
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Get a list of orders",
     *     description="Returns a list of all orders, with optional filtering by status_id.",
     *     @OA\Parameter(
     *         name="status_id",
     *         in="query",
     *         required=false,
     *         description="Filter orders by one or multiple status_id (comma-separated values, e.g., 1,2,3)",
     *         @OA\Schema(type="string", example="1,2,3")
     *     ),

     *     @OA\Response(
     *         response=400,
     *         description="Invalid status_id provided"
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Начинаем с запроса всех заказов
        $orders = Order::query();
//1   10  14
        // Если параметр status_id передан в запросе, фильтруем заказы по статусу
        if ($request->filled('status_id')) {
            $statusIds = explode(',', $request->query('status_id'));

            // Проверяем, что все status_id являются числами и существуют в таблице order_statuses
            foreach ($statusIds as $statusId) {
                if (!is_numeric($statusId) || !OrderStatus::find($statusId)) {
                    return response()->json(['message' => 'Invalid status_id provided'], 400);
                }
            }

//            masters
//            Новые заявки
            if ($statusIds[0] == 1){
                // Применяем фильтрацию по массиву status_id
                $orders = $orders->whereIn('status_id', [1]);
            }
//            Ответы
            else if ($statusIds[0] == 2){
                // Применяем фильтрацию по массиву status_id
                $orders = $orders->whereIn('status_id', [2]);
            }
//            Архив
            else if ($statusIds[0] == 3){
                // Применяем фильтрацию по массиву status_id
                $orders = $orders->whereIn('status_id', [3]);
            }
//            users
//            Отправленные
            else if ($statusIds[0] == 4){
                // Применяем фильтрацию по массиву status_id
                $orders = $orders->whereIn('status_id', [1]);
            }
//            Отвеченные
            else if ($statusIds[0] == 5){
                // Применяем фильтрацию по массиву status_id
                $orders = $orders->whereIn('status_id', [2]);
            }
//            Архив
            else if ($statusIds[0] == 6){
                // Применяем фильтрацию по массиву status_id
                $orders = $orders->whereIn('status_id', [3]);
            }

        }
        if (auth('sanctum')->user()->hasRole('master')){

            $orders = $orders
                ->where('master_id',auth('sanctum')->id())
                ->with([
                    'user_surveys.user_answers.question',
                ]) // Подгружаем связанные данные
                ->orderBy('quick_order_id')
                ->orderBy('created_at', 'desc')
                ->get();

        }else{
            // Получаем заказы, возможно с фильтрацией
            $orders = $orders
                ->where('user_id',auth('sanctum')->id())
                ->with([
                    'user_surveys.user_answers.question',
                ]) // Подгружаем связанные данные
                ->orderBy('quick_order_id')
                ->orderBy('created_at', 'desc')
                ->get();
        }
//        print(explode(',', $request->query('status_id'))[0]);
//        print('--------------------------------------');

        if(explode(',', $request->query('status_id'))[0]==4){
            $quick_orders = QuickOrder::where('is_active',1)
                ->with([
                    'user_surveys.user_answers.question',
                ]) // Подгружаем связанные данные
                ->orderBy('created_at', 'desc')
                ->get();

            foreach($orders as $order) {
                if (
                    !is_null($order->user_survey_id) &&
                    $order->relationLoaded('user_surveys') &&
                    $order->user_surveys // существует
                ) {
                    foreach($order->user_surveys->user_answers ?? [] as $user_answer) {
                        $user_answer->options = Option::whereIn(
                            'id',
                            json_decode($user_answer->option_ids ?? '[]')
                        )->get();
                    }
                }

//                $order->is_quick_order = $order->quick_order_id !== null;
                $order->is_under_video = $order->quick_order_id == null;
            }

//            return response()->json($quick_orders, Response::HTTP_OK);
//            dd();
            foreach($quick_orders as $quick_order) {
                $quick_order->video_id = null;
                $quick_order->status_id = 9;
                $quick_order->quick_order_id = $quick_order->id;
                $quick_order->master_id = 2;
                $quick_order->master_price = null;
                $quick_order->master_time = null;
                $quick_order->master_comment = null;
                $quick_order->is_read = 0;
                $quick_order->user_survey_id = $quick_order->user_surveys[0]->id;
//                $quick_order->user_survey_id = $quick_order->user_surveys[0]->id;
                $quick_order->is_quick_order = true;
                $quick_order->is_under_video = false;
                $quick_order->title = optional($quick_order->user_surveys->first())
                        ->user_answers->first()
                        ?->options_data[0]->option_text ?? null;
            }

            $combined = $orders->merge($quick_orders);

            return response()->json($combined, Response::HTTP_OK);
        }
        else
        if(explode(',', $request->query('status_id'))[0]==1){

            $quick_orders = QuickOrder::where('is_active',1)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('orders')
                        ->whereColumn('orders.quick_order_id', 'quick_orders.id')
                        ->where('orders.master_id', auth('sanctum')->id());
                })
                ->with([
                    'user_surveys.user_answers.question',
                ]) // Подгружаем связанные данные
                ->orderBy('created_at', 'desc')
                ->get();

            foreach($orders as $order) {
                if (
                    !is_null($order->user_survey_id) &&
                    $order->relationLoaded('user_surveys') &&
                    $order->user_surveys // существует
                ) {
                    foreach($order->user_surveys->user_answers ?? [] as $user_answer) {
                        $user_answer->options = Option::whereIn(
                            'id',
                            json_decode($user_answer->option_ids ?? '[]')
                        )->get();
                    }
                }

//                $order->is_quick_order = $order->quick_order_id !== null;
                $order->is_under_video = $order->quick_order_id == null;
            }
            foreach($quick_orders as $quick_order) {
                $quick_order->video_id = null;
                $quick_order->status_id = 9;
                $quick_order->quick_order_id = $quick_order->id;
                $quick_order->master_id = 2;
                $quick_order->master_price = null;
                $quick_order->master_time = null;
                $quick_order->master_comment = null;
                $quick_order->is_read = 0;
                $quick_order->user_survey_id = $quick_order->user_surveys[0]->id;
                $quick_order->is_quick_order = true;
                $quick_order->is_under_video = false;
                $quick_order->title = optional($quick_order->user_surveys->first())
                        ->user_answers->first()
                        ?->options_data[0]->option_text ?? null;


            }

            $combined = $orders->merge($quick_orders);

            return response()->json($combined, Response::HTTP_OK);
        }

        foreach($orders as $order) {
            if (
                !is_null($order->user_survey_id) &&
                $order->relationLoaded('user_surveys') &&
                $order->user_surveys // существует
            ) {
                foreach($order->user_surveys->user_answers ?? [] as $user_answer) {
                    $user_answer->options = Option::whereIn(
                        'id',
                        json_decode($user_answer->option_ids ?? '[]')
                    )->get();
                }
            }
            $order->is_under_video = $order->video_id !== null;
//            $order->is_quick_order = $order->video_id == null;
            $order->title = optional(
                $order->user_surveys?->user_answers->first()?->options[0] ?? null
            )->option_text;
//            $order->title =
//            $order->title = optional(
//                    $order->user_surveys?->user_answers->first()?->options[0]
//                )->option_text ?? null;
//            $order->title = optional(
//                    $order->user_surveys?->user_answers[0]?->options[0]
//                )->option_text ?? null;


//                optional($quick_order->user_surveys->first())
//                    ->user_answers->first()
//                    ?->options_data[0]->option_text ?? null;
        }

        return response()->json($orders, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     tags={"Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Get an order by ID",
     *     description="Returns a single order",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order found"
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/quick_orders/{quick_order_id}",
     *     tags={"Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Get an quick_order by ID",
     *     description="Returns a single quick_order",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the quick_order to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="quick_order found"
     *     ),
     *     @OA\Response(response=404, description="quick_order not found")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Create a new order",
     *     description="Adds a new order to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","user_survey_id", "master_id", "video_id", "master_price", "master_time", "status_id"},
     *             @OA\Property(property="user_survey_id", type="integer", description="ID of the user survey"),
     *             @OA\Property(property="user_id", type="integer", description="ID of the user (customer)"),
     *             @OA\Property(property="master_id", type="integer", description="ID of the master (user)"),
     *             @OA\Property(property="video_id", type="integer", description="ID of the video"),
     *             @OA\Property(property="master_price", type="string", description="Price charged by the master"),
     *             @OA\Property(property="master_time", type="string", description="Time related to the order (in minutes)"),
     *             @OA\Property(property="status_id", type="integer", description="ID of the order status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/quick_orders",
     *     tags={"Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Create a new quick_order",
     *     description="Adds a new quick_order to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","user_survey_id", "master_id", "video_id", "master_price", "master_time", "status_id"},
     *             @OA\Property(property="user_survey_id", type="integer", description="ID of the user survey"),
     *             @OA\Property(property="user_id", type="integer", description="ID of the user (customer)"),
     *             @OA\Property(property="master_id", type="integer", description="ID of the master (user)"),
     *             @OA\Property(property="video_id", type="integer", description="ID of the video"),
     *             @OA\Property(property="master_price", type="string", description="Price charged by the master"),
     *             @OA\Property(property="master_time", type="string", description="Time related to the order (in minutes)"),
     *             @OA\Property(property="status_id", type="integer", description="ID of the order status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store_quick_order(Request $request,$quick_order_id)
    {
//         return $request->all();
        // Валидация тела запроса
        $validatedData = Validator::make($request->all(), [
            'master_price' => 'required|integer',
            'master_time' => 'required|string',
        ]);


        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        // Отдельная валидация параметра из URL
        $checkQuickOrder = Validator::make(
            ['quick_order_id' => $quick_order_id],
            ['quick_order_id' => 'required|integer|exists:quick_orders,id']
        );


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

    /**
     * @OA\Put(
     *     path="/api/orders/{id}",
     *     tags={"Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Update an existing order",
     *     description="Updates order details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", description="Updated status ID (1 or 0)"),
     *             @OA\Property(property="master_price", type="number", format="float", description="Updated master price"),
     *             @OA\Property(property="master_time", type="integer", description="Updated master time"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated"
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     tags={"Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Delete an order",
     *     description="Deletes an order by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Order deleted"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $order->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Delete(
     *     path="/api/quick_orders/{id}",
     *     tags={"Orders"},
     *     security={{"sanctum": {}}},
     *     summary="Delete an quick_orders",
     *     description="Deletes an quick_orders by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the quick_orders to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="quick_orders deleted"),
     *     @OA\Response(response=404, description="quick_orders not found")
     * )
     */
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
