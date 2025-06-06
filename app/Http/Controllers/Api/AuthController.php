<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Http;
use Mobizon\MobizonApi;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\EncodedImage;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;

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
     *                 required={"phone", "city_id", "role"},
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
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
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
        $data = [
            'phone' => $this->formatPhoneNumber($request->phone),
            'city_id' => $request->city_id,
            'is_visible' => $request->is_visible,
            'photo' => $request->photo,
            'role' => $request->role,
            'username' => $request->username,
        ];
        if ($request->role == 'master'){
            $data = [
                'phone' => $this->formatPhoneNumber($request->phone),
                'city_id' => $request->city_id,
                'is_visible' => $request->is_visible,
                'photo' => $request->photo,
                'role' => $request->role,
                'iin' => $request->iin,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'company_type' => $request->company_type,
                'company_name' => $request->company_name,
            ];
        }
        $validatedData = Validator::make($data, [
            'phone' => 'required|string|max:15|unique:users',
            'city_id' => 'required|integer|exists:cities,id',
            'is_visible' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'role' => 'required|string|in:user,master',
            // Только если role = user
            'username' => [Rule::requiredIf($request->role === 'user'), 'nullable', 'string', 'max:255'],
            // Эти поля обязательны только если role = master
            'iin' => [Rule::requiredIf($request->role == 'master'), 'string', 'size:12', 'unique:users'],
            'firstname' => [Rule::requiredIf($request->role == 'master'), 'string', 'max:255'],
            'lastname' => [Rule::requiredIf($request->role == 'master'), 'string', 'max:255'],
            'company_type' => [Rule::requiredIf($request->role == 'master'), 'string', 'in:ИП,ТОО'],
            'company_name' => [Rule::requiredIf($request->role == 'master'), 'string'],
//            'description' => [Rule::requiredIf($request->role == 'master'), 'string', 'max:500'],
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), 400);
        }

        $data = $validatedData->validated();
//        // Проверка роли "master" вручную
//        if ($data['role'] === 'master') {
//            $validator = Validator::make($request->all(), [
//                'iin' => 'required|string|size:12|unique:users',
//                'firstname' => 'required|string|max:255',
//                'lastname' => 'required|string|max:255',
//                'company_type' => 'required|string|in:ИП,ТОО',
//                'company_name' => 'required|string',
//                'description' => 'required|string|max:500',
//            ]);
//
//            if ($validator->fails()) {
//                return response()->json($validator->errors(), 422);
//            }
//
//            $data = array_merge($data, $validator->validated());
//        }

        // return ($data);



        // Сохранение фото, если загружено
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/users', 'public');
//            $image = Image::make(storage_path("app/public/{$path}"))->resize(600, null, function ($constraint) {
//                $constraint->aspectRatio();
//            })->save();
            $data['photo_url'] = $path;
        }

//        $verificationCode = random_int(100000, 999999);
        $verificationCode = 000000;
//        $this->funcSendVerificationCode($data['phone'], $verificationCode);
//        formatPhoneNumber
//        $data['phone'] = $this->formatPhoneNumber($data['phone']);
//        $data['company_type'] = $data['company_type'] ?? null;
//        $data['company_name'] = $data['company_name'] ?? null;
//        $data['description'] = $data['description'] ?? null;
//        $data['username'] = $data['username'] ?? null;
        $data['verification_code'] = $verificationCode;

        if ($data['role'] === 'user') {
            $filteredData = Arr::only($data, ['phone', 'username', 'city_id', 'is_visible', 'verification_code']);
        } else { // Если role = master
            $filteredData = Arr::only($data, [
                'phone', 'iin', 'firstname', 'lastname',
                'company_type', 'company_name', 'description',
                'city_id', 'is_visible', 'verification_code'
            ]);
        }

        // Создаем пользователя с отфильтрованными данными
        try {
            $user = User::create($filteredData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ошибка при создании пользователя', 'error' => $e->getMessage()], 500);
        }
        // Сохранение кода в поле `verification_code`
        $role = Role::where('slug',$data['role'])->first();
        if ($role){
            $user->roles()->attach($role->id);
            $user->load('roles');
        }
        if ($data['role'] == 'master'){
            Rating::create(
                [
                    'master_id'=>$user->id,
                    'score'=>0,
                ]
            );
        }

        // $users = User::with('roles')->get();
        return response()->json(['message' => 'User registered successfully', 'user' => $user]);
    }

    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $this->formatPhoneNumber($request->phone);

        // Проверка, зарегистрирован ли номер телефона
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return response()->json(['error' => 'This phone number is not registered'], 404);
        }

        $code = rand(100000, 999999); // Генерация случайного кода

        $response = Http::get("https://api.mobizon.kz/service/message/sendsmsmessage", [
            'recipient' => $phone,
            'text' => "Your verification code is: $code",
            'apiKey' => env('MOBIZON_API_KEY'),
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to send verification code'], 500);
        }

        // Сохранение кода верификации для последующей проверки
        $user->verification_code = $code;
        $user->save();

        return response()->json(['message' => 'Verification code sent successfully']);
    }

    public function funcSendVerificationCode($phone, $code)
    {
        // if ($code == 0){
        //     $user = User::where('phone', $request->phone)->first();
        //     $phone = $user->phone;
        //     $code = $user->verification_code;
        // }

        $formattedPhone = $this->formatPhoneNumber($phone);

        // $api = new MobizonApi(env('MOBIZON_API_KEY'), 'api.mobizon.kz');
        $api = new MobizonApi('kzcbdfc80add4fdb9ee55e5527b427cbd82ef0f3d7ad22099b201d57acb594e0d9b2c7', 'api.mobizon.kz');

        // API call to send a message
        if ($api->call('message',
        'sendSMSMessage',
        array(
            // Recipient international phone number
            'recipient' => $formattedPhone,
            // Message text
            'text' => "Код: $code. Никому не сообщайте. mebelplace.kz",
//            'text' => "Ваш код верификации: $code. mebelplace.kz — введите код для продолжения. Не передавайте его другим.",
            // Alphaname is optional, if you don't have registered alphaname, just skip this parameter and your message will be sent with our free common alphaname, if it's available for this direction.

            // 'from' => 'SuperMakers',

            // Message will be expired after 10 min
            'params[validity]' => 1440
        ))
        ) {
        // Get message ID assigned by our system to request it's delivery report later.
        $messageId = $api->getData('messageId');

        if (!$messageId) {
            // Message is not accepted, see error code and data for details.
        }
        // Message has been accepted by API.
        } else {
            // echo $phone;
            // echo "Your verification code is: $code";
        // An error occurred while sending message
            echo '[' . $api->getCode() . '] ' . $api->getMessage() . 'See details below:' . PHP_EOL . print_r($api->getData(), true) . PHP_EOL;
        }

        // $response = Http::get("http://api.mobizon.kz/service/message/sendsmsmessage", [
        //     'recipient' => $phone,
        //     'text' => "Your verification code is: $code",
        //     'apiKey' => env('MOBIZON_API_KEY'),
        // ]);

        // if ($response->failed()) {
        //     throw new \Exception('Failed to send verification code');
        // }

    }

    public function formatPhoneNumber($phone)
    {
        // Убираем все символы, кроме цифр
        $phone = preg_replace('/\D/', '', $phone);

        // Оставляем только последние 10 цифр и добавляем "7" в начало
        return '7' . substr($phone, -10);
    }

    /**
     * Verify the user's account with a verification code.
     */
    /**
 * @OA\Post(
 *     path="/api/verify-account",
 *     summary="Верификация аккаунта по телефону и коду",
 *     description="Проверяет код верификации и отмечает аккаунт как верифицированный, если код корректен.",
 *     operationId="verifyAccount",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"phone", "verification_code"},
 *             @OA\Property(property="phone", type="string", example="+1234567890", description="Номер телефона пользователя"),
 *             @OA\Property(property="verification_code", type="integer", example=123456, description="Код верификации, отправленный на телефон")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Аккаунт успешно верифицирован",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Account verified successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Ошибка валидации или неверный код",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Invalid verification code or phone number"),
 *             @OA\Property(property="errors", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Внутренняя ошибка сервера",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Server error")
 *         )
 *     )
 * )
 */
    public function verifyAccount(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'phone' => 'required|string',
            'verification_code' => 'required|integer',
        ]);

        $phone = $this->formatPhoneNumber($request->phone);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), 400);
        }

        $user = User::where('phone', $phone)->where('verification_code', $request->verification_code)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid verification code or phone number'], 400);
        }

        // Обновляем статус на "верифицирован" и удаляем код
        // $user->update(['is_verified' => 1, 'verification_code' => null]);
        $user->is_verified = 1;
        $user->verification_code = null;
        $user->save();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['message' => 'Account verified successfully', 'Authorization' => 'Bearer ' . $token]);

        // return response()->json(['message' => 'Account verified successfully']);
    }

    /**
     * @OA\Post(
     *     path="/api/verify-code",
     *     tags={"Auth"},
     *     summary="Отправка кода для сброса пароля",
     *     description="Отправляет код на указанный номер телефона, если номер зарегистрирован в системе.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="77473186847", description="Номер телефона пользователя в формате 7XXXXXXXXXX")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Код для сброса пароля успешно отправлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Код для сброса пароля отправлен на ваш номер телефона.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка валидации или отсутствует зарегистрированный номер",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid phone number or user not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Не удалось отправить код для сброса пароля.")
     *         )
     *     )
     * )
     */
    public function verifyCode(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), 400);
        }
        // Извлечение данных
        $validatedData = $validatedData->validate();
        $phone = $this->formatPhoneNumber($validatedData['phone']);

        // Генерация кода для сброса пароля
        $resetCode = rand(100000, 999999);
        $resetCode = 000000;

        // Находим пользователя по номеру телефона
        $user = User::where('phone', $phone)->first();

        // Сохранение кода сброса пароля в базе данных для пользователя
        $user->is_verified = 0;
        $user->verification_code = $resetCode;
        $user->save();

        // Отправка кода через SMS (используем вашу функцию отправки SMS)
//        $this->funcSendVerificationCode($phone, $resetCode);

        return response()->json(['message' => 'Код для сброса пароля отправлен на ваш номер телефона.']);

        // $status = Password::sendResetLink(
        //     $request->only('phone')
        // );

        // if ($status === Password::RESET_LINK_SENT) {
        //     return response()->json(['message' => 'Ссылка на сброс пароля отправлена на ваш email.']);
        // }

        // return response()->json(['message' => 'Не удалось отправить ссылку на сброс пароля.'], 500);
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
     *                 required={"phone"},
     *                 @OA\Property(property="phone", type="string", format="", example="+1234567890"),
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
     *                 }
     *             )
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validatedData = Validator::make($request->all(), [

            'phone' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(),400);
        }
        $validatedData = $validatedData->validate();

        $phone = $this->formatPhoneNumber($request->phone);
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['Authorization' => 'Bearer ' . $token]);
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

//    /**
//     * @OA\Post(
//     *     path="/api/password/reset-link",
//     *     tags={"Auth"},
//     *     summary="Отправка кода для сброса пароля",
//     *     description="Отправляет код для сброса пароля на указанный номер телефона, если номер зарегистрирован в системе.",
//     *     @OA\RequestBody(
//     *         required=true,
//     *         @OA\JsonContent(
//     *             required={"phone"},
//     *             @OA\Property(property="phone", type="string", example="77473186847", description="Номер телефона пользователя в формате 7XXXXXXXXXX")
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=200,
//     *         description="Код для сброса пароля успешно отправлен",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="message", type="string", example="Код для сброса пароля отправлен на ваш номер телефона.")
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=400,
//     *         description="Ошибка валидации или отсутствует зарегистрированный номер",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="error", type="string", example="Invalid phone number or user not found.")
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=500,
//     *         description="Ошибка сервера",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="message", type="string", example="Не удалось отправить код для сброса пароля.")
//     *         )
//     *     )
//     * )
//     */
//    public function sendResetLink(Request $request)
//    {
//        $validatedData = Validator::make($request->all(), [
//            'phone' => 'required|string',
//        ]);
//
//        if ($validatedData->fails()) {
//            return response()->json($validatedData->errors(), 400);
//        }
//        // Извлечение данных
//        $validatedData = $validatedData->validate();
//        $phone = $this->formatPhoneNumber($validatedData['phone']);
//
//        // Генерация кода для сброса пароля
//        $resetCode = rand(100000, 999999);
//
//        // Находим пользователя по номеру телефона
//        $user = User::where('phone', $phone)->first();
//
//        // Сохранение кода сброса пароля в базе данных для пользователя
//        $user->is_verified = 0;
//        $user->verification_code = $resetCode;
//        $user->save();
//
//        // Отправка кода через SMS (используем вашу функцию отправки SMS)
//        $this->funcSendVerificationCode($phone, $resetCode);
//
//        return response()->json(['message' => 'Код для сброса пароля отправлен на ваш номер телефона.']);
//
//        // $status = Password::sendResetLink(
//        //     $request->only('phone')
//        // );
//
//        // if ($status === Password::RESET_LINK_SENT) {
//        //     return response()->json(['message' => 'Ссылка на сброс пароля отправлена на ваш email.']);
//        // }
//
//        // return response()->json(['message' => 'Не удалось отправить ссылку на сброс пароля.'], 500);
//    }
//
//    /**
//     * @OA\Post(
//     *     path="/api/password/reset",
//     *     tags={"Auth"},
//     *     summary="Сброс пароля",
//     *     description="Сбрасывает пароль пользователя, если номер телефона и код верификации совпадают.",
//     *     @OA\RequestBody(
//     *         required=true,
//     *         @OA\JsonContent(
//     *             required={"phone", "verification_code"},
//     *             @OA\Property(property="phone", type="string", example="77473186847", description="Номер телефона пользователя в формате 7XXXXXXXXXX"),
//     *             @OA\Property(property="verification_code", type="integer", example=123456, description="Код для верификации пользователя"),
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=200,
//     *         description="Пароль успешно изменен",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="message", type="string", example="Пароль успешно изменен.")
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=400,
//     *         description="Ошибка валидации или неверный код",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="message", type="string", example="Неверный код верификации или номер телефона.")
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=500,
//     *         description="Ошибка сервера",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="message", type="string", example="Не удалось сбросить пароль.")
//     *         )
//     *     )
//     * )
//     */
//    public function reset(Request $request)
//    {
//        $validatedData = Validator::make($request->all(), [
//            'phone' => 'required|string',
//            'verification_code' => 'required|integer',
//
//        ]);
//
//        if ($validatedData->fails()) {
//            return response()->json($validatedData->errors(),400);
//        }
//        $validatedData = $validatedData->validate();
//        $phone = $this->formatPhoneNumber($validatedData['phone']);
//
//        // Находим пользователя по номеру телефона и проверяем код верификации
//        $user = User::where('phone', $phone)->where('verification_code', $validatedData['verification_code'])->first();
//
//        if (!$user) {
//            return response()->json(['message' => 'Неверный код верификации или номер телефона.'], 400);
//        }
//        // Обновляем пароль и сбрасываем код верификации
//        $user->verification_code = null; // Удаляем код после успешного сброса пароля
//        $user->is_verified = 1;
//        $user->save();
//
//        return response()->json(['message' => 'Пароль успешно изменен.']);
//        // $status = Password::reset(
//        //     $request->only('phone', 'password', 'password_confirmation', 'token'),
//        //     function ($user, $password) {
//        //         $user->password = Hash::make($password);
//        //         $user->save();
//        //     }
//        // );
//
//        // if ($status === Password::PASSWORD_RESET) {
//        //     return response()->json(['message' => 'Пароль успешно изменен.']);
//        // }
//
//        // return response()->json(['message' => 'Не удалось сбросить пароль.'], 500);
//    }


}
