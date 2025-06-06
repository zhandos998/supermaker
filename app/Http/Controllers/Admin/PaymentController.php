<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Role;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PaymentController extends Controller
{
    public function index()
    {
//        $payments = Payment::all();
        $payments = Payment::with(['user', 'status'])->get();
//        dd($payments);
//        return $payments;
        return view('admin.payments.index', compact('payments'));
    }
    // Форма для создания нового пользователя
    public function create()
    {
        $cities = City::all();
        return view('admin.payments.create', compact('cities'));
    }

    // Сохранение нового пользователя
    public function store(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:payments,email',
            'phone' => 'required|string|unique:payments,phone',
            'paymentname' => 'required|string|unique:payments,paymentname',
            'city_id' => 'required|integer|exists:cities,id', // Убедитесь, что город существует
            'iin' => 'required|string|size:12|unique:payments,iin', // Пример для ИИН
            'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Валидация для фотографии
            'is_visible' => 'nullable|boolean', // Если это поле не обязательно
            'password' => 'required|string|min:8|confirmed', // Валидация пароля
        ]);

        $validatedDataRole = $request->validate([
            'role' => 'required|string|in:admin,master,payment', // Валидация для роли
        ]);

        $role = Role::where('slug', $validatedDataRole['role'])->first();

        $validatedData['is_visible'] = $request->has('is_visible');

        $validatedData['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $filePath = $request->file('photo')->store('photos', 'public'); // Сохранение в директорию storage/app/public/photos
            $validatedData['photo_url'] = $filePath; // Добавление URL файла к данным
        }

        $payment = Payment::create($validatedData);

        $payment->roles()->attach($role);

        return redirect()->route('admin.payments.index')->with('success', 'Payment created successfully.');
        // return "Success";
    }

    // Отображение информации о пользователе
    public function show(Payment $payment)
    {
        return view('admin.payments.show', compact('payment'));
    }

    // Форма для редактирования пользователя
    public function edit(Payment $payment)
    {
        $cities = City::all();
        return view('admin.payments.edit', compact('payment','cities'));
    }

    // Обновление информации о пользователе
    public function update(Request $request, Payment $payment)
    {
//        dd($request);
        $request->validate([
            'email' => 'nullable|email|unique:payments,email,' . $payment->id,
            'password' => 'nullable|min:6',
            'phone' => 'required|unique:payments,phone,' . $payment->id,
            'paymentname' => 'required|unique:payments,paymentname,' . $payment->id,
            'city_id' => 'required|exists:cities,id',
            'firstname' => 'required',
            'lastname' => 'required',
            'iin' => 'required|unique:payments,iin,' . $payment->id,
            'is_visible' => 'nullable|boolean',
            'photo_url' => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
        ]);

        $payment->update($request->only([
            'email', 'phone', 'paymentname', 'city_id', 'firstname', 'lastname', 'iin', 'is_visible', 'photo_url', 'wallet', 'videos_count'
        ]));

        if ($request->filled('password')) {
            $payment->password = Hash::make($request->password);
            $payment->save();
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment updated successfully.');
    }

    // Удаление пользователя
    public function destroy(Payment $payment)
    {
        $payment->delete();
        return redirect()->route('admin.payments.index')->with('success', 'Payment deleted successfully.');
    }
}
