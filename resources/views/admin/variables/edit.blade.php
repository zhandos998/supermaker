@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit Variable') }}</div>

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
                    <form action="{{ route('admin.variables.update', $variable->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="firstname">{{ __('Name') }}</label>
                            <input type="text" class="form-control" name="name" value="{{ $variable->name }}" required>
                        </div>

                        <div class="form-group">
                            <label for="lastname">{{ __('Value') }}</label>
                            <input type="text" class="form-control" name="value" value="{{ $variable->value }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('Update Variable') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
