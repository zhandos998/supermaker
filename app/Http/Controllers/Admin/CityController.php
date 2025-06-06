<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::with('country')->get();
        return view('admin.cities.index', compact('cities'));
    }
    // Форма для создания нового пользователя
    public function create()
    {
        $countries = Country::all();
//        $cities = City::with('country')->get();
        return view('admin.cities.create', compact(['countries']));
    }

    // Сохранение нового пользователя
    public function store(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:cities,name', // Город должен быть уникальным
            'country_id' => 'required|integer|exists:countries,id',// Страна должна существовать
        ]);

        $city = City::create($validatedData);

        return redirect()->route('admin.cities.index')->with('success', 'City created successfully.');
        // return "Success";
    }

    // Отображение информации о пользователе
    public function show(City $city)
    {
        return view('admin.cities.show', compact('city'));
    }

    // Форма для редактирования пользователя
    public function edit(City $city)
    {
//        dd($city);
//        $city = $city->with('country')->first();
        $countries = Country::all();
        return view('admin.cities.edit', compact('city','countries'));
    }

    // Обновление информации о пользователе
    public function update(Request $request, City $city)
    {
//        dd($request);
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255|unique:cities,name', // Город должен быть уникальным
            'country_id' => 'nullable|integer|exists:countries,id',// Страна должна существовать
        ]);

        $city->update($request->only([
            'name', 'country_id'
        ]));

        return redirect()->route('admin.cities.index')->with('success', 'City updated successfully.');
    }

    // Удаление пользователя
    public function destroy(City $city)
    {
        $city->delete();
        return redirect()->route('admin.cities.index')->with('success', 'City deleted successfully.');
    }
}
