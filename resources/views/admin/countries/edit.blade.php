@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit Country') }}</div>

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
                    <form action="{{ route('admin.countries.update', $country->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">{{ __('Name') }}</label>
                            <input type="text" class="form-control" name="name" value="{{ $country->name }}">
                        </div>

                        <div class="form-group">
                            <label for="code">{{ __('Code') }}</label>
                            <input type="text" class="form-control" name="code" value="{{ $country->code }}">

                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('Update Country') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
