<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class MainController extends Controller
{
    /**
     * @OA\PathItem(
     *     path="/api/v1/users",
     *     @OA\Get(
     *         summary="Получение списка пользователей",
     *         description="Возвращает список всех зарегистрированных пользователей",
     *         operationId="getUsersList",
     *         tags={"Users"},
     *         @OA\Response(
     *             response=200,
     *             description="Успешный запрос",
     *             @OA\JsonContent(
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/User")
     *             )
     *         ),
     *         @OA\Response(
     *             response=401,
     *             description="Не авторизован"
     *         )
     *     )
     * )
     */
    public function index()
    {
        // Логика для получения списка пользователей
    }

    /**
     * Store a newly created resource in storage.
     
    public function store(Request $request)
    {
        //
    }*/

    /**
     * Display the specified resource.

    public function show(string $id)
    {
        //
    }*/

    /**
     * Update the specified resource in storage.

    public function update(Request $request, string $id)
    {
        //
    }*/

    /**
     * Remove the specified resource from storage.

    public function destroy(string $id)
    {
        //
    }*/
}
