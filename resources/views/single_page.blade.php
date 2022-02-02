@extends('layouts.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection

@section('content')

    <section class="page-wrap"> <!-- explore categories -->

        <div class="page-header section-bg-gradient">
            <div class="container">
                <div class="row">
                    <div class="col-md-10 mx-auto">
                        <h1>{!! $page->title !!}</h1>
                    </div>
                </div>
            </div>
        </div>


        <div class="container">
            <div class="row">
                <div class="col-md-10 mx-auto">
                    {!! safe_output($page->post_content) !!}
                </div>
            </div>

        </div>
    </section> <!-- #explore categories -->

@endsection
