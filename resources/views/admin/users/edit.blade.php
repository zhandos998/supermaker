@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit User') }}</div>

                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="firstname">{{ __('First Name') }}</label>
                            <input type="text" class="form-control" name="firstname" value="{{ $user->firstname }}" required>
                        </div>

                        <div class="form-group">
                            <label for="lastname">{{ __('Last Name') }}</label>
                            <input type="text" class="form-control" name="lastname" value="{{ $user->lastname }}" required>
                        </div>

                        <div class="form-group">
                            <label for="email">{{ __('Email') }}</label>
                            <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                        </div>

                        <div class="form-group">
                            <label for="password">{{ __('Password') }} (leave blank to keep the same)</label>
                            <input type="password" class="form-control" name="password">
                        </div>

                        <div class="form-group">
                            <label for="phone">{{ __('Phone') }}</label>
                            <input type="text" class="form-control" name="phone" value="{{ $user->phone }}" required>
                        </div>

                        <div class="form-group">
                            <label for="username">{{ __('Username') }}</label>
                            <input type="text" class="form-control" name="username" value="{{ $user->username }}" required>
                        </div>

                        <div class="form-group">
                            <label for="city_id">{{ __('City') }}</label>
                            <select name="city_id" class="form-control" required>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" {{ $city->id == $user->city_id ? 'selected' : '' }}>{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="iin">{{ __('IIN') }}</label>
                            <input type="text" class="form-control" name="iin" value="{{ $user->iin }}" required>
                        </div>

                        <div class="form-group">
                            <label for="is_visible">{{ __('Visible') }}</label>
                            <input type="checkbox" name="is_visible" {{ $user->is_visible ? 'checked' : '' }}>
                        </div>

                        <div class="form-group">
                            <label for="photo_url">{{ __('Photo URL') }}</label>
                            <input type="text" class="form-control" name="photo_url" value="{{ $user->photo_url }}">
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('Update User') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
