@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Payment Statuses') }}</div>

                <div class="card-body">
                    <a href="{{ route('admin.payment_statuses.create') }}" class="btn btn-outline-success mb-3">{{ __('Add New Payment Status') }}</a>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment_statuses as $payment_status)
                            <tr>
                                <td>{{ $payment_status->id }}</td>
                                <td>{{ $payment_status->name }}</td>
                                <td style="width: 300px">
{{--                                    <a href="{{ route('admin.payment_statuses.show', $payment_status->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('View') }}</a>--}}
                                    <a href="{{ route('admin.payment_statuses.edit', $payment_status->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('Edit') }}</a>
                                    <form action="{{ route('admin.payment_statuses.destroy', $payment_status->id) }}" method="POST" style="display: inline-block;">
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
