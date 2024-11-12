@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Add New User') }}</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.users.store') }}" method="POST"  enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="firstname">{{ __('First Name') }}</label>
                            <input type="text" class="form-control" name="firstname" required>
                        </div>

                        <div class="form-group">
                            <label for="lastname">{{ __('Last Name') }}</label>
                            <input type="text" class="form-control" name="lastname" required>
                        </div>

                        <div class="form-group">
                            <label for="email">{{ __('Email') }}</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="password">{{ __('Password') }}</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">{{ __('Confirm Password') }}</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">{{ __('Phone') }}</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>

                        <div class="form-group">
                            <label for="username">{{ __('Username') }}</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>

                        <div class="form-group">
                            <label for="city_id">{{ __('City') }}</label>
                            <select name="city_id" class="form-control" required>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="iin">{{ __('IIN') }}</label>
                            <input type="text" class="form-control" name="iin" required>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" name="is_visible" value="1">
                            <label class="form-check-label" for="is_visible">{{ __('Is Visible') }}</label>
                        </div>

                        <div class="form-group">
                            <label for="photo">{{ __('Upload Photo') }}</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label for="role">{{ __('Role') }}</label>
                            <select name="role" class="form-control" required>
                                <option value="admin">{{ __('Admin') }}</option>
                                <option value="master">{{ __('Master') }}</option>
                                <option value="user" selected>{{ __('User') }}</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('Create User') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
