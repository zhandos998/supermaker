@extends('layouts.app')

@section('content')
<div class="container-flued">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body" style="align-self: center;">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                        <a href="/admin/users" class="btn btn-primary">{{__('Пользователи')}}</a>
                        <a href="/admin/payments" class="btn btn-primary">{{__('Оплаты')}}</a>
                        <a href="/admin/payment_statuses" class="btn btn-primary">{{__('Статусы оплаты')}}</a><br>
                        <a href="/admin/videos" class="btn btn-primary">{{__('Видео')}}</a>
                        <a href="/admin/cities" class="btn btn-primary">{{__('Города')}}</a>
                        <a href="/admin/countries" class="btn btn-primary">{{__('Страны')}}</a><br>
{{--                        <a href="/admin/order_reports" class="btn btn-primary">{{__('order_reports')}}</a>--}}
{{--                        <a href="/admin/order_statuses" class="btn btn-primary">{{__('order_statuses')}}</a><br>--}}
{{--                        <a href="/admin/files" class="btn btn-primary">{{__('files')}}</a>--}}
{{--                        <a href="/admin/cities" class="btn btn-primary">{{__('cities')}}</a>--}}
{{--                        <a href="/admin/countries" class="btn btn-primary">{{__('countries')}}</a><br>--}}
{{--                        <a href="/admin/ratings" class="btn btn-primary">{{__('ratings')}}</a>--}}
{{--                        <a href="/admin/roles" class="btn btn-primary">{{__('roles')}}</a>--}}
{{--                        <a href="/admin/stores" class="btn btn-primary">{{__('stores')}}</a><br>--}}
{{--                        <a href="/admin/surveys" class="btn btn-primary">{{__('surveys')}}</a>--}}
{{--                        <a href="/admin/tags" class="btn btn-primary">{{__('tags')}}</a>--}}
                        <a href="/admin/variables" class="btn btn-primary">{{__('Variables')}}</a><br>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
