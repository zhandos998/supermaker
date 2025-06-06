<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Role;
use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VariableController extends Controller
{
    public function index()
    {
//        $variables = Variable::all();
        $variables = Variable::get();
//        dd($variables);
//        return $variables;
        return view('admin.variables.index', compact('variables'));
    }
    // Форма для создания нового пользователя
    public function create()
    {
        $cities = City::all();
        return view('admin.variables.create', compact('cities'));
    }

    // Сохранение нового пользователя
    public function store(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $validatedDataRole = $request->validate([
            'role' => 'required|string|in:admin,master,variable', // Валидация для роли
        ]);

        $role = Role::where('slug', $validatedDataRole['role'])->first();

        $validatedData['is_visible'] = $request->has('is_visible');

        $validatedData['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $filePath = $request->file('photo')->store('photos', 'public'); // Сохранение в директорию storage/app/public/photos
            $validatedData['photo_url'] = $filePath; // Добавление URL файла к данным
        }

        $variable = Variable::create($validatedData);

        $variable->roles()->attach($role);

        return redirect()->route('admin.variables.index')->with('success', 'Variable created successfully.');
        // return "Success";
    }

    // Отображение информации о пользователе
    public function show(Variable $variable)
    {
        return view('admin.variables.show', compact('variable'));
    }

    // Форма для редактирования пользователя
    public function edit(Variable $variable)
    {
        $cities = City::all();
        return view('admin.variables.edit', compact('variable','cities'));
    }

    // Обновление информации о пользователе
    public function update(Request $request, Variable $variable)
    {
//        dd($request);
        $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $variable->update($request->only([
            'name', 'value'
        ]));

        return redirect()->route('admin.variables.index')->with('success', 'Variable updated successfully.');
    }

    // Удаление пользователя
    public function destroy(Variable $variable)
    {
        $variable->delete();
        return redirect()->route('admin.variables.index')->with('success', 'Variable deleted successfully.');
    }
}
