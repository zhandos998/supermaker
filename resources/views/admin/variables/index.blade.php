@extends('layouts.app')

@section('content')
<div class="container-flued">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Variables') }}</div>

                <div class="card-body">
{{--                    <a href="{{ route('admin.variables.create') }}" class="btn btn-outline-success mb-3">{{ __('Add New Variable') }}</a>--}}

                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('value') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($variables as $variable)
                            <tr>
                                <td>{{ $variable->id }}</td>
                                <td>{{ $variable->name }}</td>
                                <td>{{ $variable->value }}</td>
                                <td>
{{--                                    <a href="{{ route('admin.variables.show', $variable->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('View') }}</a>--}}
                                    <a href="{{ route('admin.variables.edit', $variable->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('Edit') }}</a>
                                    <form action="{{ route('admin.variables.destroy', $variable->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirmDelete(event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('Delete') }}</button>
                                    </form>

                                    <script>
                                        function confirmDelete(event) {
                                            event.preventDefault(); // Останавливаем отправку формы
                                            if (confirm("Вы уверены, что хотите удалить этот платеж?")) {
                                                event.target.submit(); // Если пользователь подтвердил, отправляем форму
                                            }
                                        }
                                    </script>
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
