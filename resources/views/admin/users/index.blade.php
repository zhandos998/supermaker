@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header">{{ __('Users') }}</div>

                <div class="card-body">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-outline-success mb-3">{{ __('Add New User') }}</a>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Имя') }}</th>
                                <th>{{ __('Номер телефона') }}</th>
                                <th>{{ __('Ник') }}</th>
                                <th>{{ __('Кошелек') }}</th>
                                <th>{{ __('Количество дополнительных видео') }}</th>
                                <th>{{ __('Пополнить') }}</th>
                                <th>{{ __('Действия') }}</th>
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

                                    <form action="{{ route('admin.users.topup', $user->id) }}" method="POST" style="display:inline-flex; align-items: center; gap: 4px; margin-top: 5px;">
                                        @csrf
                                        <input type="number" name="amount" class="form-control form-control-sm" placeholder="0" style="width: 80px;" required>
                                        <button type="submit" class="btn btn-outline-success btn-sm">{{ __('Пополнить') }}</button>
                                    </form>
                                </td>
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
