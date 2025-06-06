@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Edit Video') }}</div>

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
                        <form action="{{ route('admin.videos.update', $video->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="title">{{ __('Title') }}</label>
                                <input type="text" class="form-control" name="title" value="{{ $video->title }}">
                            </div>

                            <div class="form-group">
                                <label for="furniture_type">{{ __('Furniture Type') }}</label>
                                <input type="text" class="form-control" name="furniture_type" value="{{ $video->furniture_type }}">
                            </div>

                            <div class="form-group">
                                <label for="description">{{ __('Description') }}</label>
                                <textarea class="form-control" name="description">{{ $video->description }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="url">{{ __('Video URL') }}</label>
                                <input type="file" class="form-control" name="url">
                            </div>

                            <div class="form-group">
                                <label for="price">{{ __('Price') }}</label>
                                <input type="number" step="0.01" class="form-control" name="price" value="{{ $video->price }}">
                            </div>

                            <div class="form-group">
                                <label for="sizes">{{ __('Sizes') }}</label>
                                <input type="text" class="form-control" name="sizes" value="{{ $video->sizes }}">
                            </div>

                            <div class="form-group">
                                <label for="is_fixed">{{ __('Fixed') }}</label>
                                <input type="checkbox" name="is_fixed" value="1" {{ $video->is_fixed ? 'checked' : '' }}>
                            </div>

                            <div class="form-group">
                                <label for="preview_url">{{ __('Preview Image') }}</label>
                                <input type="file" class="form-control" name="preview_url">
                            </div>

                            <div class="form-group">
                                <label for="is_visible">{{ __('Visible') }}</label>
                                <input type="checkbox" name="is_visible" value="1" {{ $video->is_visible ? 'checked' : '' }}>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ __('Update Video') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
