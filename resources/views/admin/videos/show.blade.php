@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Video Details') }}</div>

                <div class="card-body">
                    <div class="mb-3">
                        <strong>{{ __('First Name') }}:</strong> {{ $video->firstname }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Last Name') }}:</strong> {{ $video->lastname }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Email') }}:</strong> {{ $video->email }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Phone') }}:</strong> {{ $video->phone }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Videoname') }}:</strong> {{ $video->videoname }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('City') }}:</strong> {{ $video->city->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('IIN') }}:</strong> {{ $video->iin }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Visible') }}:</strong> {{ $video->is_visible ? 'Yes' : 'No' }}
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Photo URL') }}:</strong> <img src="{{ asset('storage/' . $video->photo_url) }}" alt="{{ $video->firstname }} {{ $video->lastname }}" style="max-width: 200px;">
                    </div>


                    <a href="{{ route('admin.videos.edit', $video->id) }}" class="btn btn-primary me-2">{{ __('Edit Video') }}</a>
                    <a href="{{ route('admin.videos.index') }}" class="btn btn-secondary me-2">{{ __('Back to Videos') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
