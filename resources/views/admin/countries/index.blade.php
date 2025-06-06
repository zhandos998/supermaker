@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Countries') }}</div>

                <div class="card-body">
                    <a href="{{ route('admin.countries.create') }}" class="btn btn-outline-success mb-3">{{ __('Add New Country') }}</a>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('code') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($countries as $country)
                            <tr>
                                <td>{{ $country->id }}</td>
                                <td>{{ $country->name }}</td>
                                <td>{{ $country->code }}</td>
                                <td style="width: 100px">
{{--                                    <a href="{{ route('admin.countries.show', $country->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('View') }}</a>--}}
                                    <a href="{{ route('admin.countries.edit', $country->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('Edit') }}</a>
                                    <form action="{{ route('admin.countries.destroy', $country->id) }}" method="POST" style="display: inline-block;">
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
