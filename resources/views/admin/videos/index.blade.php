@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Videos') }}</div>

                <div class="card-body" style="overflow-x: scroll;">
{{--                    <a href="{{ route('admin.videos.create') }}" class="btn btn-outline-success mb-3">{{ __('Add New Video') }}</a>--}}

                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('User Name') }}</th>
                                <th>{{ __('title') }}</th>
                                <th>{{ __('furniture_type') }}</th>
                                <th>{{ __('description') }}</th>
                                <th>{{ __('url') }}</th>
                                <th>{{ __('price') }}</th>
                                <th>{{ __('sizes') }}</th>
                                <th>{{ __('is_fixed') }}</th>
                                <th>{{ __('views_count') }}</th>
                                <th>{{ __('preview_url') }}</th>
                                <th>{{ __('is_visible') }}</th>
                                <th>{{ __('tapped_count') }}</th>
                                <th>{{ __('created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($videos as $video)
                            <tr>
                                <td>{{ $video->id }}</td>
                                <td>{{ $video->user_id }}</td>
                                <td>{{ $video->title }}</td>
                                <td>{{ $video->furniture_type }}</td>
                                <td>{{ $video->description }}</td>
                                <td>
                                    <a href="https://supermakers.pro/storage/{{ $video->url }}">
                                        https://supermakers.pro/storage/{{ $video->url }}
                                    </a>
                                </td>
                                <td>{{ $video->price }}</td>
                                <td>{{ $video->sizes }}</td>
                                <td>{{ $video->is_fixed }}</td>
                                <td>{{ $video->views_count }}</td>
                                <td>
                                    <a href="https://supermakers.pro/storage/{{ $video->preview_url }}">
                                        https://supermakers.pro/storage/{{ $video->preview_url }}
                                    </a>
                                </td>
                                <td>{{ $video->is_visible }}</td>
                                <td>{{ $video->tapped_count }}</td>
                                <td>{{ $video->created_at }}</td>
                                <td>
{{--                                    <a href="{{ route('admin.videos.show', $video->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('View') }}</a>--}}
                                    <a href="{{ route('admin.videos.edit', $video->id) }}" class="btn btn-outline-primary btn-sm me-2">{{ __('Edit') }}</a>
                                    <form action="{{ route('admin.videos.destroy', $video->id) }}" method="POST" style="display: inline-block;">
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
