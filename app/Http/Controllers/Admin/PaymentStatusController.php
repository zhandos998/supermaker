<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Role;
use App\Models\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PaymentStatusController extends Controller
{
    public function index()
    {
        $payment_statuses = PaymentStatus::all();
        return view('admin.payment_statuses.index', compact('payment_statuses'));
    }
    // Форма для создания нового пользователя
    public function create()
    {
        $cities = City::all();
        return view('admin.payment_statuses.create', compact('cities'));
    }

    // Сохранение нового пользователя
    public function store(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $payment_status = PaymentStatus::create($validatedData);

        return redirect()->route('admin.payment_statuses.index')->with('success', 'Payment_status created successfully.');
        // return "Success";
    }

    // Отображение информации о пользователе
    public function show(PaymentStatus $payment_status)
    {
        return view('admin.payment_statuses.show', compact('payment_status'));
    }

    // Форма для редактирования пользователя
    public function edit(PaymentStatus $payment_status)
    {
        $cities = City::all();
        return view('admin.payment_statuses.edit', compact('payment_status','cities'));
    }

    // Обновление информации о пользователе
    public function update(Request $request, PaymentStatus $payment_status)
    {
//        dd($request);
        $request->validate([
            'name' => 'nullable|string|',
        ]);

        $payment_status->update($request->only([
            'name',
        ]));


        return redirect()->route('admin.payment_statuses.index')->with('success', 'Payment_status updated successfully.');
    }

    // Удаление пользователя
    public function destroy(PaymentStatus $payment_status)
    {
        $payment_status->delete();
        return redirect()->route('admin.payment_statuses.index')->with('success', 'Payment_status deleted successfully.');
    }
}
