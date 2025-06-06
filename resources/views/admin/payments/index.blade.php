@extends('layouts.app')

@section('content')
<div class="container-flued">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Payments') }}</div>

                <div class="card-body">
{{--                    <a href="{{ route('admin.payments.create') }}" class="btn btn-outline-success mb-3">{{ __('Add New Payment') }}</a>--}}

                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('User Name') }}</th>
                                <th>{{ __('amount') }}</th>
                                <th>{{ __('check_url') }}</th>
                                <th>{{ __('status_id') }}</th>
                                <th>{{ __('description') }}</th>
                                <th>{{ __('created_at') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->id }}</td>
                                <td>{{ $payment->user->firstname }} {{ $payment->user->lastname }}</td>
                                <td>{{ $payment->amount }}</td>
                                <td><a href="{{ $payment->check_url }}">{{ $payment->check_url }}</a></td>
                                <td>{{ $payment->status->name }}</td>
                                <td>{{ $payment->description }}</td>
                                <td>{{ $payment->created_at }}</td>
                                <td>
{{--                                    <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('View') }}</a>--}}
{{--                                    <a href="{{ route('admin.payments.edit', $payment->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('Edit') }}</a>--}}
                                    <form action="{{ route('admin.payments.destroy', $payment->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirmDelete(event)">
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
