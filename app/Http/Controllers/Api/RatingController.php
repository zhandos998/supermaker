<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating; // Ensure you have a Rating model
use App\Models\Variable; // Ensure you have a Rating model
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RatingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/ratings",
     *     tags={"Ratings"},
     *     security={{"sanctum": {}}},
     *     summary="Get a list of ratings",
     *     description="Returns a list of all ratings.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $ratings = Rating::all();
        return response()->json($ratings, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/ratings/{id}",
     *     tags={"Ratings"},
     *     security={{"sanctum": {}}},
     *     summary="Get a rating by ID",
     *     description="Returns a single rating",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the rating to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rating found"
     *     ),
     *     @OA\Response(response=404, description="Rating not found")
     * )
     */
    public function show($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($rating, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/ratings",
     *     tags={"Ratings"},
     *     security={{"sanctum": {}}},
     *     summary="Create a new rating",
     *     description="Adds a new rating to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"value", "user_id", "product_id"},
     *             @OA\Property(property="value", type="integer", example=4, description="Rating value"),
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID of the user"),
     *             @OA\Property(property="product_id", type="integer", example=1, description="ID of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rating created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'value' => 'required|integer|between:1,5', // Assuming ratings are between 1 and 5
            'user_id' => 'required|integer|exists:users,id',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $rating = Rating::create($validatedData->validated());
        return response()->json($rating, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/ratings/{id}",
     *     tags={"Ratings"},
     *     security={{"sanctum": {}}},
     *     summary="Update an existing rating",
     *     description="Updates rating details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the rating to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="value", type="integer", example=5, description="Updated rating value"),
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID of the user"),
     *             @OA\Property(property="product_id", type="integer", example=1, description="ID of the product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rating updated"
     *     ),
     *     @OA\Response(response=404, description="Rating not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'value' => 'nullable|integer|between:1,5',
            'user_id' => 'nullable|integer|exists:users,id',
            'product_id' => 'nullable|integer|exists:products,id',
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $rating->update(array_filter($validatedData->validated())); // Update only provided fields
        return response()->json($rating, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/ratings/{id}",
     *     tags={"Ratings"},
     *     security={{"sanctum": {}}},
     *     summary="Delete a rating",
     *     description="Deletes a rating by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the rating to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Rating deleted"),
     *     @OA\Response(response=404, description="Rating not found")
     * )
     */
    public function destroy($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], Response::HTTP_NOT_FOUND);
        }

        $rating->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
//
//    public function CalculateMasterRatings($id=0)
//    {
//        $variables = Variable::whereIn('id', [2, 3, 4, 5])->pluck('value', 'id');
//        $K1 = $variables[2] ?? 0;
//        $K2 = $variables[3] ?? 0;
//        $K3 = $variables[4] ?? 0;
//        $K4 = $variables[5] ?? 0;
//
//        $fiveDaysAgo = Carbon::now()->subDays(5);
//
//        $masters = Master::pluck('id');
//
//        $ratings = [];
//
//        foreach ($masters as $masterId) {
//            // Получаем P1, P2, P3, P4 за последние 5 дней
//            $P1 = $this->calculateClickRate($masterId, $fiveDaysAgo);
//            $P2 = $this->calculateReadRate($masterId, $fiveDaysAgo);
//            $P3 = $this->calculateReplyRate($masterId, $fiveDaysAgo);
//            $P4 = $this->calculateOrderRate($masterId, $fiveDaysAgo);
//
//            // Рассчитываем рейтинг
//            $score = ($K1 * $P1) + ($K2 * $P2) + ($K3 * $P3) + ($K4 * $P4);
//
//            $ratings[] = [
//                'master_id' => $masterId,
//                'score' => $score,
//                'updated_at' => now()
//            ];
//        }
//
//
//        // Массовое обновление или вставка
//        Rating::upsert($ratings, ['master_id'], ['score', 'updated_at']);
//
////        Log::info('Master ratings calculated successfully.');
//
////        return response()->json(null, Response::HTTP_NO_CONTENT);
//    }
//
//    // P1 — % кликов на ролик от числа показов в ленте
//    private function calculateClickRate($masterId, $fromDate)
//    {
//        $views = DB::table('video_views')->where('master_id', $masterId)->where('created_at', '>=', $fromDate)->count();
//        $clicks = DB::table('profile_clicks')->where('master_id', $masterId)->where('created_at', '>=', $fromDate)->count();
//        return $views ? ($clicks / $views) * 100 : 0;
//    }
//
//    // P2 — % прочитанных запросов от общего количества полученных
//    private function calculateReadRate($masterId, $fromDate)
//    {
//        $totalRequests = DB::table('requests')->where('master_id', $masterId)->where('created_at', '>=', $fromDate)->count();
//        $readRequests = DB::table('requests')->where('master_id', $masterId)->where('is_read', true)->where('created_at', '>=', $fromDate)->count();
//        return $totalRequests ? ($readRequests / $totalRequests) * 100 : 0;
//    }
//
//    // P3 — % ответов мастера от общего числа запросов
//    private function calculateReplyRate($masterId, $fromDate)
//    {
//        $totalRequests = DB::table('requests')->where('master_id', $masterId)->where('created_at', '>=', $fromDate)->count();
//        $replies = DB::table('replies')->where('master_id', $masterId)->where('created_at', '>=', $fromDate)->count();
//        return $totalRequests ? ($replies / $totalRequests) * 100 : 0;
//    }
//
//    // P4 — % заказов от общего числа отправленных ответов
//    private function calculateOrderRate($masterId, $fromDate)
//    {
//        $totalReplies = DB::table('replies')->where('master_id', $masterId)->where('created_at', '>=', $fromDate)->count();
//        $orders = DB::table('orders')->where('master_id', $masterId)->where('created_at', '>=', $fromDate)->count();
//        return $totalReplies ? ($orders / $totalReplies) * 100 : 0;
//    }

}
