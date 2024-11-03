<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="0.1",
 * ),
 *  @OA\Server(
 *      description="SuperMakers",
 *      url="http://127.0.0.1:8000"
 *  ),
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"User"},
     *      @OA\Response(
     *          response=200,
     *         description="A list of users",
     *      ),
     *     @OA\PathItem (
     *     ),
     * )
     */

    public function index()
    {
        $users = User::all();
        return response()->json(compact('users'), 200);
    }

    /**
     * @OA\Get(
     *     path="/api/users/create",
     *     tags={"User"},
     *     summary="Show the form for creating a new user",
     *     @OA\Response(
     *         response=200,
     *         description="Show form to create user"
     *     ),
     *     @OA\PathItem (
     *     ),
     * )
     */
    // Форма для создания нового пользователя
    public function create()
    {
        // $cities = City::all();
        // $roles = Role::all();
        return response()->json();
    }
    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"User"},
     *     summary="Store a newly created user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *             required={"firstname","lastname","email","password","is_visible","phone","username","city_id","iin","password_confirmation", "role"},
     *             @OA\Property(property="firstname", type="string", example="John"),
     *             @OA\Property(property="lastname", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="city_id", type="integer", example=1),
     *             @OA\Property(property="iin", type="string", maxLength=12, example="123456789012"),
     *             @OA\Property(property="is_visible", type="integer", enum={1, 0}, example=1),
     *             @OA\Property(
     *                 property="photo",
     *                 type="string",
     *                 format="binary",
     *                 description="Photo file upload",
     *             ),
     *             @OA\Property(property="role", type="string", enum={"master", "user"}, example="master")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\PathItem (
     *     ),
     * )
     */
    // Сохранение нового пользователя
    public function store(Request $request)
    {
        // dd($request);

        $validatedData = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'username' => 'required|string|unique:users,username',
            'city_id' => 'required|integer|exists:cities,id', // Убедитесь, что город существует
            'iin' => 'required|string|size:12|unique:users,iin', // Пример для ИИН
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Валидация для фотографии
            'is_visible' => 'nullable|boolean', // Если это поле не обязательно
            'password' => 'required|string|min:8|confirmed', // Валидация пароля
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(),400);
        }
        $validatedData = $validatedData->validate();

        $validatedDataRole = Validator::make($request->all(), [
            'role' => 'nullable|string|in:admin,master,user', // Валидация для роли
        ]);

        if ($validatedDataRole->fails()) {
            return response()->json($validatedDataRole->errors(),400);
        }
        $validatedDataRole = $validatedDataRole->validate();

        $role = Role::where('slug', $validatedDataRole['role'])->first();

        $validatedData['is_visible'] = $request->has('is_visible');

        $validatedData['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $filePath = $request->file('photo')->store('photos', 'public'); // Сохранение в директорию storage/app/public/photos
            $validatedData['photo_url'] = $filePath; // Добавление URL файла к данным
        }

        $user = User::create($validatedData);

        $user->roles()->attach($role);

        return response()->json(['message' => 'User registered successfully', 'user' => $user]);
        // return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }
    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Display the specified user",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *     ),
     *     @OA\Response(response=400, description="User not found"),
     *     @OA\PathItem (
     *     ),
     * )
     */
    // Отображение информации о пользователе
    public function show(User $user)
    {
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(compact('user'));
    }


    /**
     * @OA\Get(
     *     path="/api/users/{id}/edit",
     *     tags={"User"},
     *     summary="Show the form for editing the specified user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Show form to edit user"
     *     ),
     *     @OA\PathItem (
     *     ),
     * )
     */
    // Форма для редактирования пользователя
    public function edit(User $user)
    {
        // $cities = City::all();
        return response()->json(compact('user'));
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update user information",
     *     description="Update user profile details",
     *     operationId="updateUser",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="firstname", type="string", example="John"),
     *             @OA\Property(property="lastname", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="city_id", type="integer", example=1),
     *             @OA\Property(property="iin", type="string", maxLength=12, example="123456789012"),
     *             @OA\Property(property="is_visible", type="integer", enum={1, 0}, example=1),
     *             @OA\Property(
     *                 property="photo",
     *                 type="string",
     *                 format="binary",
     *                 description="Photo file upload"
     *             ),
     *             @OA\Property(property="role", type="string", enum={"master", "user"}, example="master")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    // Обновление информации о пользователе
    public function update(Request $request, User $user)
    {
        $validatedData = Validator::make($request->all(), [
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'username' => 'nullable|string|unique:users,username',
            'city_id' => 'nullable|integer|exists:cities,id', // Убедитесь, что город существует
            'iin' => 'nullable|string|size:12|unique:users,iin', // Пример для ИИН
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Валидация для фотографии
            'is_visible' => 'nullable|boolean', // Если это поле не обязательно
            'password' => 'nullable|string|min:8|confirmed', // Валидация пароля
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(),400);
        }
        $validatedData = $validatedData->validate();


        if ($request->filled('firstname')) {
            $user->firstname = $request->firstname;
        }
        if ($request->filled('lastname')) {
            $user->lastname = $request->lastname;
        }
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        if ($request->filled('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->filled('username')) {
            $user->username = $request->username;
        }
        if ($request->filled('city_id')) {
            $user->city_id = $request->city_id;
        }
        if ($request->filled('iin')) {
            $user->iin = $request->iin;
        }
        if ($request->filled('photo')) {
            $user->photo = $request->photo;
        }
        if ($request->filled('is_visible')) {
            $user->is_visible = $request->is_visible;
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password );
        }
        $user->save();

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }
    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"User"},
     *     summary="Remove the specified user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(response=400, description="User not found"),
     *     @OA\PathItem (
     *     ),
     * )
     */
    // Удаление пользователя
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully.', 'user' => $user]);
    }
}
