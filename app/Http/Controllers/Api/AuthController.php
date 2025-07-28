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
            'iin' => ['nullable', 'string', 'size:12', 'unique:users'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'company_type' => ['nullable', 'string', 'in:ИП,ТОО'],
            'company_name' => ['nullable', 'string'],
        ], [
            'phone.unique' => 'Такой номер телефона уже зарегистрирован.',
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }

        $data = $validatedData->validated();

        // Сохранение фото, если загружено
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/users', 'public');

            $data['photo_url'] = $path;
        }

        $verificationCode = random_int(100000, 999999);
//        $verificationCode = 000000;
        $this->funcSendVerificationCode($data['phone'], $verificationCode);

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

        $user->update(['device_token' => $request->device_token]);

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
        $formattedPhone = $this->formatPhoneNumber($phone);

        $api = new MobizonApi('kzcbdfc80add4fdb9ee55e5527b427cbd82ef0f3d7ad22099b201d57acb594e0d9b2c7', 'api.mobizon.kz');

        if ($api->call('message',
        'sendSMSMessage',
        array(
            'recipient' => $formattedPhone,
            'text' => "Код: $code. Никому не сообщайте. mebelplace.kz",

            'params[validity]' => 1440
        ))
        ) {
        $messageId = $api->getData('messageId');

        if (!$messageId) {
        }
        } else {
            echo '[' . $api->getCode() . '] ' . $api->getMessage() . 'See details below:' . PHP_EOL . print_r($api->getData(), true) . PHP_EOL;
        }
    }

    public function formatPhoneNumber($phone)
    {
        // Убираем все символы, кроме цифр
        $phone = preg_replace('/\D/', '', $phone);

        // Оставляем только последние 10 цифр и добавляем "7" в начало
        return '7' . substr($phone, -10);
    }

    public function verifyAccount(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'phone' => 'required|string',
            'verification_code' => 'required|integer',
        ]);

        $phone = $this->formatPhoneNumber($request->phone);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
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

    public function verifyCode(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }
        // Извлечение данных
        $validatedData = $validatedData->validate();
        $phone = $this->formatPhoneNumber($validatedData['phone']);

        // Генерация кода для сброса пароля
        $resetCode = rand(100000, 999999);
//        $resetCode = 000000;

        // Находим пользователя по номеру телефона
        $user = User::where('phone', $phone)->first();

        // Сохранение кода сброса пароля в базе данных для пользователя
        $user->is_verified = 0;
        $user->verification_code = $resetCode;
        $user->save();

        // Отправка кода через SMS (используем вашу функцию отправки SMS)
        $this->funcSendVerificationCode($phone, $resetCode);

        return response()->json(['message' => 'Код для сброса пароля отправлен на ваш номер телефона.']);
    }

    public function login(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            $errorText = implode("\n", $validatedData->errors()->all());

            return response()->json([
                'message' => 'Данные недопустимы.'.$errorText,
                'errors' => $errorText, // это строка
            ], 422);
        }
        $validatedData = $validatedData->validate();

        $phone = $this->formatPhoneNumber($request->phone);
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Данные недопустимы.'.'Пользователь не зарегистрирован!',
                'errors' => 'Пользователь не зарегистрирован!', // это строка
            ], 404);
        }

        $token = $user->createToken('api-token')->plainTextToken;
        $user->update(['device_token' => $request->device_token]);

        $verificationCode = random_int(100000, 999999);
        $this->funcSendVerificationCode($phone, $verificationCode);

        $user->update(['verification_code' => $verificationCode]);

        return response()->json(['user' => $user]);
//        return response()->json(['Authorization' => 'Bearer ' . $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
