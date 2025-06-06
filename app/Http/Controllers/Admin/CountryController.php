<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Role;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::all();
        return view('admin.countries.index', compact('countries'));
    }
    // Форма для создания нового пользователя
    public function create()
    {
        $cities = City::all();
        return view('admin.countries.create', compact('cities'));
    }

    // Сохранение нового пользователя
    public function store(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:countries,name',
            'code' => 'required|string|max:255|unique:countries,code',
        ]);

        $country = Country::create($validatedData);

        return redirect()->route('admin.countries.index')->with('success', 'Country created successfully.');
        // return "Success";
    }

    // Отображение информации о пользователе
    public function show(Country $country)
    {
        return view('admin.countries.show', compact('country'));
    }

    // Форма для редактирования пользователя
    public function edit(Country $country)
    {
        $cities = City::all();
        return view('admin.countries.edit', compact('country','cities'));
    }

    // Обновление информации о пользователе
    public function update(Request $request, Country $country)
    {
//        dd($request);

        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255|unique:countries,name',
            'code' => 'nullable|string|max:255|unique:countries,code',
        ]);

        $country->update($request->only([
            'name', 'code',
        ]));

        return redirect()->route('admin.countries.index')->with('success', 'Country updated successfully.');
    }

    // Удаление пользователя
    public function destroy(Country $country)
    {
        $country->delete();
        return redirect()->route('admin.countries.index')->with('success', 'Country deleted successfully.');
    }
}
