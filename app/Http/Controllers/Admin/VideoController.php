<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Role;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::all();
        return view('admin.videos.index', compact('videos'));
    }
    // Форма для создания нового пользователя
    public function create()
    {
        $cities = City::all();
        return view('admin.videos.create', compact('cities'));
    }

    // Сохранение нового пользователя
    public function store(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:videos,email',
            'phone' => 'required|string|unique:videos,phone',
            'videoname' => 'required|string|unique:videos,videoname',
            'city_id' => 'required|integer|exists:cities,id', // Убедитесь, что город существует
            'iin' => 'required|string|size:12|unique:videos,iin', // Пример для ИИН
            'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Валидация для фотографии
            'is_visible' => 'nullable|boolean', // Если это поле не обязательно
            'password' => 'required|string|min:8|confirmed', // Валидация пароля
        ]);

        $validatedDataRole = $request->validate([
            'role' => 'required|string|in:admin,master,video', // Валидация для роли
        ]);

        $role = Role::where('slug', $validatedDataRole['role'])->first();

        $validatedData['is_visible'] = $request->has('is_visible');

        $validatedData['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $filePath = $request->file('photo')->store('photos', 'public'); // Сохранение в директорию storage/app/public/photos
            $validatedData['photo_url'] = $filePath; // Добавление URL файла к данным
        }

        $video = Video::create($validatedData);

        $video->roles()->attach($role);

        return redirect()->route('admin.videos.index')->with('success', 'Video created successfully.');
        // return "Success";
    }

    // Отображение информации о пользователе
    public function show(Video $video)
    {
        return view('admin.videos.show', compact('video'));
    }

    // Форма для редактирования пользователя
    public function edit(Video $video)
    {
        $cities = City::all();
        return view('admin.videos.edit', compact('video','cities'));
    }

    // Обновление информации о пользователе
    public function update(Request $request, Video $video)
    {
//        dd($request);
        $request->validate([
            'title' => 'nullable|string|max:255',
            'furniture_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|mimetypes:video/quicktime,video/mp4,video/webm,video/x-flv,video/mpeg,video/x-ms-asf,application/x-mpegURL,video/MP2T,video/3gpp,video/x-msvideo,video/x-ms-wmv|file|max:204800',
            'price' => 'nullable|numeric',
            'sizes' => 'nullable|string',
            'is_fixed' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'preview_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',

        ]);


        $video->update($request->only([
            'title', 'furniture_type', 'description',  'firstname', 'price', 'sizes', 'is_visible', 'is_fixed',
        ]));

        // Обработка загрузки видео
        if ($request->hasFile('url')) {
            // Удаляем старый файл, если он существует
            if ($video->url) {
                Storage::delete($video->url);
            }
            // Сохраняем новый файл и получаем путь
            $data['url'] = $request->file('url')->store('videos', 'public');
        }

        // Обработка загрузки превью (изображения)
        if ($request->hasFile('preview_url')) {
            if ($video->preview_url) {
                Storage::delete($video->preview_url);
            }
            $data['preview_url'] = $request->file('preview_url')->store('previews', 'public');
        }

        // Обновление видео
        $video->update($data);

        return redirect()->route('admin.videos.index')->with('success', 'Video updated successfully.');
    }

    // Удаление пользователя
    public function destroy(Video $video)
    {
        $video->delete();
        return redirect()->route('admin.videos.index')->with('success', 'Video deleted successfully.');
    }
}
