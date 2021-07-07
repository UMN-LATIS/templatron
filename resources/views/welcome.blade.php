@extends('base')

@section('content')
<div class="container mt-2">
<div class="jumbotron">
    <h1 class="display-3">Welcome to Templatron</h1>
    <p class="lead">Templatron is a simple tool to apply templates to your Canvas site.</p>
    <hr class="my-2">
    <p>We recomend using Templatron with newly created sites. Templates won't overwrite your existing content, but if you apply one to an already-populated site, it might make things messy. If you'd like to learn more about Templatron, <a href="http://umn-latis.github.io/templatron/">visit our help section</a>, which includes an <a href="https://www.youtube.com/watch?v=xzqQxj3vCTk">introductory screencast</a>. </p>
    <p class="lead text-center">
        <a class="btn btn-primary btn-lg" href="{{ route("go") }}" role="button">Let's Go!</a>
    </p>
</div>
</div>



@endsection

@section('header')

@endsection