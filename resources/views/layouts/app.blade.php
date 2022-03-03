@extends('laravel-usp-theme::master')

{{-- @section('title', config('app.name')) --}}

{{-- @section('content_header')
    <h1></h1>
@stop --}}

@section('flash')
    @parent
    @include('partials.alerts')
@endsection
