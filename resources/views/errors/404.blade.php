@extends('layouts.app')
@section('title') @lang('app.not_found_404') | @parent @endsection

@section('content')

    <div class="page-header section-bg-gradient">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1> @lang('app.error_404')</h1>
                    <p>{!! $exception->getMessage() !!}</p>
                </div>
            </div>
        </div>
    </div>


    <section class="categories-wrap wrap-404"> <!-- explore categories -->
        <div class="container">

            <div class="row">
                <div class="col-md-8 mx-auto">
                    <img src="{{asset('assets/images/404.jpg')}}" class="img-fluid" />

                    <p class="error-404-btn-wrap">
                        <a href="{{route('browse_campaigns')}}" class="btn hu-btn-filled btn-lg">@lang('app.browse_campaign')</a>
                    </p>
                </div>
            </div>

        </div>
    </section> <!-- #explore categories -->

@endsection
