@extends('layouts.app')

@section('content')
    <article class="card prose">
        <h1>{{ $title }}</h1>
        {!! $contentHtml !!}
    </article>
@endsection