<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/subscriptions",
     *     tags={"Subscriptions"},
     *     security={{"sanctum": {}}},
     *     summary="Get a list of subscriptions",
     *     description="Returns a list of all subscription items for the authenticated user.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    // Получение всех подписок
    public function index()
    {
        $subscriptions = Subscription::with(['user', 'master'])->get();
        return response()->json($subscriptions);
    }

    /**
     * @OA\Post(
     *     path="/api/subscriptions",
     *     tags={"Subscriptions"},
     *     security={{"sanctum": {}}},
     *     summary="Add a subscription item",
     *     description="Adds a new item to the user's subscriptions list",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"master_id"},
     *             @OA\Property(property="master_id", type="integer", description="ID of the master to subscription"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="subscription item created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    // Создание подписки
    public function store(Request $request)
    {

        $validatedData = Validator::make($request->all(),[
            'master_id' => 'required|integer|exists:users,id', // Ensure the item exists
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $subscription = Subscription::firstOrCreate([
            'user_id' => auth()->id(),
            'master_id' => $request->master_id
        ]);
        return response()->json($subscription, 201);
    }
    /**
     * @OA\Delete(
     *     path="/api/subscriptions/{id}",
     *     tags={"Subscriptions"},
     *     security={{"sanctum": {}}},
     *     summary="Remove a subscription item",
     *     description="Removes an item from the user's subscriptions list",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the subscription item to remove",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="subscription item deleted"),
     *     @OA\Response(response=404, description="subscription item not found")
     * )
     */
    // Удаление подписки
    public function destroy($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->delete();

        return response()->json(['message' => 'Subscription deleted successfully']);
    }
}
