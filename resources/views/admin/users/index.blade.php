@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Users') }}</div>

                <div class="card-body">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-outline-success mb-3">{{ __('Add New User') }}</a>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Username') }}</th>
                                <th>{{ __('wallet') }}</th>
                                <th>{{ __('videos_count') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->wallet }}</td>
                                <td>{{ $user->videos_count }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('View') }}</a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('Edit') }}</a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
