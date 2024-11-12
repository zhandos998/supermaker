@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('User Details') }}</div>

                <div class="card-body">
                    <div class="mb-3">
                        <strong>{{ __('First Name') }}:</strong> {{ $user->firstname }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Last Name') }}:</strong> {{ $user->lastname }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Email') }}:</strong> {{ $user->email }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Phone') }}:</strong> {{ $user->phone }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Username') }}:</strong> {{ $user->username }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('City') }}:</strong> {{ $user->city->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('IIN') }}:</strong> {{ $user->iin }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Visible') }}:</strong> {{ $user->is_visible ? 'Yes' : 'No' }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Photo URL') }}:</strong> <img src="{{ asset('storage/' . $user->photo_url) }}" alt="{{ $user->firstname }} {{ $user->lastname }}" style="max-width: 200px;">
                    </div>


                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary me-2">{{ __('Edit User') }}</a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary me-2">{{ __('Back to Users') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
