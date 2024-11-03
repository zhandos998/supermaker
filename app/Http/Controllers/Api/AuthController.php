<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     description="Register a new user with required information",
     *     operationId="registerUser",
     *     tags={"Auth"},
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
     *                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="city_id", type="integer", example=1),
     *                 @OA\Property(property="firstname", type="string", example="John"),
     *                 @OA\Property(property="lastname", type="string", example="Doe"),
     *                 @OA\Property(property="iin", type="string", example="123456789012", maxLength=12),
     *                 @OA\Property(property="is_visible", type="integer", enum={1, 0}, example=1),
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Upload photo file"
     *                 ),
     *                 @OA\Property(property="role", type="string", example="user", enum={"master", "user"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="city_id", type="integer", example=1),
     *                 @OA\Property(property="firstname", type="string", example="John"),
     *                 @OA\Property(property="lastname", type="string", example="Doe"),
     *                 @OA\Property(property="iin", type="string", example="123456789012"),
     *                 @OA\Property(property="is_visible", type="integer", enum={1, 0}, example=1),
     *                 @OA\Property(property="photo_url", type="string", example="/storage/photos/abc123.jpg"),
     *                 @OA\Property(property="role", type="string", example="user")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",

     *     )
     * )
     */

    public function register(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // Обратите внимание на подтверждение пароля
            'phone' => 'required|string|max:15|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'city_id' => 'required|integer|exists:cities,id',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'iin' => 'required|string|size:12|unique:users',
            'is_visible' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Валидация для файла фото
            'role' => 'required|string' // Убедитесь, что роли соответствуют вашим значениям
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(),400);

        }
        $validatedData = $validatedData->validate();

        // dd($validatedData);
        // Загрузка фото, если предоставлено
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public'); // сохранение в storage/app/public/photos
            $validatedData['photo_url'] = $path; // Сохранение пути в базе данных
        }

        // Хэшируем пароль перед сохранением
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Создаем пользователя
        $user = User::create($validatedData);

        return response()->json(['message' => 'User registered successfully', 'user' => $user]);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login",
     *     description="Authenticate user and provide a token",
     *     operationId="loginUser",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email", "password"},
     *                 @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *                 @OA\Property(property="password", type="string", example="password123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="abc123token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "email": {"The email field is required."},
     *                     "password": {"The password field is required."}
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validatedData = Validator::make($request->all(), [

            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(),400);
        }
        $validatedData = $validatedData->validate();
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['Authorization' => 'Bearer ' + $token]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User logout",
     *     description="Logs out the authenticated user and deletes their current access token",
     *     operationId="logoutUser",
     *     tags={"Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * @OA\Post(
     *     path="/api/password/reset-link",
     *     summary="Send password reset link",
     *     description="Sends a password reset link to the user's email",
     *     operationId="sendResetLink",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ссылка на сброс пароля отправлена на ваш email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="array",
     *                 @OA\Items(type="string", example="The email field is required.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send password reset link",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Не удалось отправить ссылку на сброс пароля.")
     *         )
     *     )
     * )
     */
    public function sendResetLink(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(),400);
        }
        $validatedData = $validatedData->validate();

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Ссылка на сброс пароля отправлена на ваш email.']);
        }

        return response()->json(['message' => 'Не удалось отправить ссылку на сброс пароля.'], 500);
    }

    /**
     * @OA\Post(
     *     path="/api/password/reset",
     *     summary="Reset password",
     *     description="Resets the user's password using a reset token",
     *     operationId="resetPassword",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "email", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", example="abcdef123456"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пароль успешно изменен.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="array",
     *                 @OA\Items(type="string", example="The token field is required.")
     *             ),
     *             @OA\Property(property="email", type="array",
     *                 @OA\Items(type="string", example="The email field is required.")
     *             ),
     *             @OA\Property(property="password", type="array",
     *                 @OA\Items(type="string", example="The password must be at least 8 characters.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to reset password",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Не удалось сбросить пароль.")
     *         )
     *     )
     * )
     */
    public function reset(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(),400);
        }
        $validatedData = $validatedData->validate();

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Пароль успешно изменен.']);
        }

        return response()->json(['message' => 'Не удалось сбросить пароль.'], 500);
    }

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
    public function show(Request $request)
    {
        // Возвращаем информацию о текущем пользователе
        return response()->json($request->user());
    }
}
