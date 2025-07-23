<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Role;
use App\Models\User;
use App\Models\ProfileClick;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="0.1",
 * ),
 *  @OA\Server(
 *      description="SuperMakers",
 *      url="https://mebelplace.kz"
 *  ),
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 * )
 */
class UserController extends Controller
{

//    public function trackProfileClick(Request $request, $masterId)
//    {
//        ProfileClick::create([
//            'master_id' => $masterId,
//            'user_id' => auth()->id(),
//            'ip' => $request->ip(),
//        ]);
//
//        return response()->json(['message' => 'Profile click recorded']);
//    }
    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get current user information",
     *     description="Returns information about the currently authenticated user",
     *     operationId="getUserInfo",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User information retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="city_id", type="integer", example=1),
     *             @OA\Property(property="firstname", type="string", example="John"),
     *             @OA\Property(property="lastname", type="string", example="Doe"),
     *             @OA\Property(property="iin", type="string", example="123456789012"),
     *             @OA\Property(property="is_visible", type="integer", example=1),
     *             @OA\Property(property="photo_url", type="string", example="/storage/photos/johndoe.jpg"),
     *             @OA\Property(property="role", type="string", example="user"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function show_current_user(Request $request)
    {
        // Возвращаем информацию о текущем пользователе
        return response()->json(auth('sanctum')->user()->load('roles'));
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"User"},
     *     security={{"sanctum": {}}},
     *     summary="Get a list of users",
     *     description="Retrieve a list of users with their roles.",
     *     @OA\Response(
     *         response=200,
     *         description="List of users with roles",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function index(Request $request)
    {
        $users = User::with('roles')->get();
        return response()->json($users);
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"User"},
     *     summary="Store a newly created user",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "email", "password", "password_confirmation", "phone", "username", "city_id", "firstname", "lastname", "iin", "role"},
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *                 @OA\Property(property="password", type="string", example="password123"),
     *                 @OA\Property(property="password_confirmation", type="string", example="password123"),
     *                 @OA\Property(property="phone", type="string", example="+77473186847"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="city_id", type="integer", example=1),
     *                 @OA\Property(property="firstname", type="string", example="John"),
     *                 @OA\Property(property="lastname", type="string", example="Doe"),
     *                 @OA\Property(property="iin", type="string", example="123456789014", maxLength=12),
     *                 @OA\Property(property="is_visible", type="integer", enum={1, 0}, example=1),
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Upload photo file"
     *                 ),
     *                 @OA\Property(property="role", type="string", example="user", enum={"master", "user"}),
     *                 @OA\Property(property="company_type", type="string", enum={"ИП", "ТОО"}, example="ИП"),
     *                 @OA\Property(property="company_name", type="string", example="My Company"),
     *                 @OA\Property(property="description", type="string", example="Professional service provider")
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
        $validatedData = Validator::make($request->all(), [
            'phone' => 'required|string|max:15|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'city_id' => 'required|integer|exists:cities,id',
            'is_visible' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Валидация для файла фото
            'role' => 'required|string|in:user,master',

//            'name' => 'required|string|max:255',
//            'email' => 'required|string|email|max:255|unique:users',
//            'password' => 'required|string|min:8|confirmed', // Обратите внимание на подтверждение пароля
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }


        $data = $validatedData->validated();
        if ($data['role'] === 'master') {
            $validator = Validator::make($request->all(), [
                'iin' => 'required|string|size:12|unique:users',
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'company_type' => 'required|string|in:ИП,ТОО',
                'company_name' => 'required|string',
                'description' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                $errorText = implode("\n", $validatedData->errors()->all());

                return response()->json([
                    'message' => 'Данные недопустимы.'.$errorText,
                    'errors' => $errorText, // это строка
                ], 422);
            }

            $data = array_merge($data, $validator->validated());
        }

        // return ($data);

        // $verificationCode = random_int(100000, 999999);


        // $verificationService = new AuthController(); // Инициализация сервиса
        // $verificationService->funcSendVerificationCode($data['phone'], $verificationCode);
        // $this->funcSendVerificationCode($data['phone'], $verificationCode);


        // Сохранение фото, если загружено
        if ($request->hasFile('photo')) {
            $data['photo_url'] = $request->file('photo')->store('photos/users', 'public');
        }

        // Хэшируем пароль перед сохранением
        $data['password'] = Hash::make($data['password']);
        $data['phone'] = $data['phone'];
        // $data['phone'] = $this->formatPhoneNumber($data['phone']);
        $data['company_type'] = $data['company_type'] ?? null;
        $data['company_name'] = $data['company_name'] ?? null;
        $data['description'] = $data['description'] ?? null;
        // $data['verification_code'] = $verificationCode;

        $user = User::create($data);

        // Сохранение кода в поле `verification_code`
        $role = Role::where('slug',$data['role'])->first();
        if ($role){
            $user->roles()->attach($role->id);
            $user->load('roles');
        }

        return response()->json(['message' => 'User registered successfully', 'user' => $user]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Display the specified user",
     *     tags={"User"},
     *     security={{"sanctum": {}}},
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
    public function show($id)
    {
        $user = User::with('roles')->find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}",
     *     summary="Update user information",
     *     description="Update user profile details",
     *     operationId="updateUser",
     *     tags={"User"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"_method"},
     *                 @OA\Property(property="_method", type="string", example="PUT", description="Hidden field to simulate PUT method"),
     *                 @OA\Property(property="firstname", type="string", example="John"),
     *                 @OA\Property(property="lastname", type="string", example="Doe"),
     *                 @OA\Property(property="password", type="string", example="password123"),
     *                 @OA\Property(property="password_confirmation", type="string", example="password123"),
     *                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="city_id", type="integer", example=1),
     *                 @OA\Property(property="iin", type="string", maxLength=12, example="123456789012"),
     *                 @OA\Property(property="is_visible", type="integer", enum={1, 0}, example=1),
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Photo file upload"
     *                 ),
     *                 @OA\Property(property="role", type="string", enum={"master", "user"}, example="master"),
     *                 @OA\Property(property="company_type", type="string", enum={"ИП", "ТОО"}, example="ИП"),
     *                 @OA\Property(property="company_name", type="string", example="My Company"),
     *                 @OA\Property(property="description", type="string", example="Professional service provider")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    // Обновление информации о пользователе
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Основная валидация
        $validatedData = Validator::make($request->all(), [
            'phone' => 'nullable|string|max:15|unique:users,phone,' . $user->id,
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'city_id' => 'nullable|integer|exists:cities,id',
            'is_visible' => 'nullable|integer|in:0,1',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'role' => 'nullable|string|in:user,master',
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $data = $validatedData->validated();

        // Проверяем, если роль master
        if ($user->role === 'master' || ($request->filled('role') && $request->role === 'master')) {
            $additionalValidation = Validator::make($request->all(), [
                'company_type' => 'nullable|string|in:ИП,ТОО',
                'company_name' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:500',
                'iin' => 'nullable|string|size:12|unique:users',
                'firstname' => 'nullable|string|max:255',
                'lastname' => 'nullable|string|max:255',
            ]);

            if ($additionalValidation->fails()) {
                $errorText = implode("\n", $additionalValidation->errors()->all());

                return response()->json([
                    'message' => 'Данные недопустимы.'.$errorText,
                    'errors' => $errorText, // это строка
                ], 422);
            }

            $data = array_merge($data, $additionalValidation->validated());
        }

        if (isset($data['role'])) {
            // Удаляем предыдущие роли
            $user->roles()->detach();

            // Находим новую роль и добавляем её
            $newRole = Role::where('slug', $data['role'])->first();
            if ($newRole) {
                $user->roles()->attach($newRole->id);
                // $user->role = $data['role']; // Обновляем значение в объекте
            }
        }

        // Обновление данных
        foreach ($data as $key => $value) {
            if ($key === 'password') {
                $user->password = Hash::make($value);
            } elseif ($key === 'photo' && $request->hasFile('photo')) {
                // Удаляем предыдущую фотографию, если она существует
                if ($user->photo_url && Storage::disk('public')->exists($user->photo_url)) {
                    Storage::disk('public')->delete($user->photo_url);
                }

                // Сохраняем новую фотографию
                $user->photo_url = 'https://mebelplace.kz/storage/'.$request->file('photo')->store('photos/users', 'public');
            } elseif ($key === 'role') {
                continue;
            } else {
                $user->$key = $value;
            }
        }

        $user->save();

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"User"},
     *     summary="Remove the specified user",
     *     security={{"sanctum": {}}},
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
     *     @OA\Response(response=400, description="User not found")
     * )
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully.', 'user' => $user]);
    }

}
