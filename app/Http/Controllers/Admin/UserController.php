<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }
    // Форма для создания нового пользователя
    public function create()
    {
        $cities = City::all();
        return view('admin.users.create', compact('cities'));
    }

    // Сохранение нового пользователя
    public function store(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'username' => 'required|string|unique:users,username',
            'city_id' => 'required|integer|exists:cities,id', // Убедитесь, что город существует
            'iin' => 'required|string|size:12|unique:users,iin', // Пример для ИИН
            'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Валидация для фотографии
            'is_visible' => 'nullable|boolean', // Если это поле не обязательно
            'password' => 'required|string|min:8|confirmed', // Валидация пароля
        ]);

        $validatedDataRole = $request->validate([
            'role' => 'required|string|in:admin,master,user', // Валидация для роли
        ]);

        $role = Role::where('slug', $validatedDataRole['role'])->first();

        $validatedData['is_visible'] = $request->has('is_visible');

        $validatedData['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $filePath = $request->file('photo')->store('photos', 'public'); // Сохранение в директорию storage/app/public/photos
            $validatedData['photo_url'] = $filePath; // Добавление URL файла к данным
        }

        $user = User::create($validatedData);

        $user->roles()->attach($role);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
        // return "Success";
    }

    // Отображение информации о пользователе
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    // Форма для редактирования пользователя
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // Обновление информации о пользователе
    public function update(Request $request, User $user)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'phone' => 'required|unique:users,phone,' . $user->id,
            'username' => 'required|unique:users,username,' . $user->id,
            'city_id' => 'required|exists:cities,id',
            'firstname' => 'required',
            'lastname' => 'required',
            'iin' => 'required|unique:users,iin,' . $user->id,
            'is_visible' => 'nullable|boolean',
            'photo_url' => 'nullable|url',
        ]);

        $user->update($request->only([
            'email', 'phone', 'username', 'city_id', 'firstname', 'lastname', 'iin', 'is_visible', 'photo_url'
        ]));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    // Удаление пользователя
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
