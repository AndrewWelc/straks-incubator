@extends('layouts.app')

@section('content')



    <div class="page-header section-bg-gradient">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>{!! $title !!}</h1>
                    <p>
                        {!! str_replace(['[results_count]', '[search_times]'], [number_format($campaigns->total()), $search_time], __('app.search_results_info')) !!}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">

        <div class="row">

            @if($campaigns->count() > 0)
                <div class="box-campaign-lists">

                    @foreach($campaigns as $nc)
                        @php
                            $percent_raised = $nc->percent_raised();
                        @endphp

                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 box-campaign-item">
                            <div class="box-campaign">
                                <div class="box-campaign-content">

                                    <div class="box-campaign-img">
                                        <a href="{{route('campaign_single', $nc->slug)}}"> <img src="{{ $nc->feature_img_url()->feature_image }}" /></a>
                                    </div>

                                    <div class="box-campaign-description">
                                        <h4>
                                            <a href="{{route('campaign_single', $nc->slug)}}"> {{$nc->title}} </a>
                                        </h4>

                                        <ul>
                                            <li><img src="{!! $nc->user->get_gravatar() !!}"> {{$nc->user->name}}</li>
                                            <li>{!! $percent_raised !!}% {!! __('app.funded') !!}</li>
                                        </ul>
                                    </div>

                                    <div class="box-campaign-summery">
                                        <ul>
                                            <li><strong>{{$nc->days_left()}}</strong> @lang('app.days_left')</li>
                                            <li class="text-center"><strong>{!! get_amount($nc->success_payments->sum('amount')) !!}</strong> @lang('app.funded')</li>
                                            <li class="text-right"> <strong>{!! get_amount($nc->goal) !!}</strong> {!! __('app.goal') !!} </li>
                                        </ul>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>  <!-- #box-campaign-lists -->

            @else
                <div class="payment-received">
                    <h1><i class="fa fa-exclamation-triangle"></i> @lang('app.no_campaigns_to_display')</h1>
                    <a href="{{route('browse_campaigns')}}" class="btn btn-lg-filled">@lang('app.browse_campaign')</a>
                </div>
            @endif

        </div>


        <div class="page-pagination-wrap">
            <div class="row">
                <div class="col-md-12">
                    {{$campaigns->appends(['q' => $q])->links()}}
                </div>
            </div>
        </div>


    </div><!-- /.container -->
@endsection
