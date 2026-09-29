@extends('layouts.app')

@section('main_class', 'main--reading')

@section('content')
    <article class="card prose">
        <h1>{{ $title }}</h1>
        {!! $contentHtml !!}
    </article>
@endsection