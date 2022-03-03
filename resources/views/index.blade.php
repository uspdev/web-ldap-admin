@extends('layouts.app')

@section('content')
    @auth
        Acesse o menu acima com as opções
    @else
        Você ainda não fez seu login com a senha única USP <a href="{{ url('/login') }}"> Faça seu Login! </a>
    @endauth
@endsection
