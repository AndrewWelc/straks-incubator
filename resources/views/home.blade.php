@extends('layouts.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection

@section('content')

    <div class="home-featured-campaign">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 col-12">

                    <h1>{{$featured_campaign->title}}</h1>

                    <div class="progress">
                        @php
                            $percent_raised = $featured_campaign->percent_raised();
                        @endphp
                        <div class="progress-bar" role="progressbar" aria-valuenow="{{$percent_raised}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$percent_raised <= 100 ? $percent_raised : 100}}%;">
                            {{$percent_raised}}%
                        </div>
                    </div>

                    <div class="featured-campaign-summery">
                        <ul>
                            <li><strong>{{$featured_campaign->days_left()}}</strong> @lang('app.days_left')</li>
                            <li><strong>{{$featured_campaign->success_payments->count()}}</strong> @lang('app.backers')</li>
                            <li><strong>{!! get_amount($featured_campaign->goal) !!}</strong> @lang('app.goal')</li>
                            <li><strong>{!! get_amount($featured_campaign->success_payments->sum('amount')) !!}</strong> @lang('app.funded')</li>
                        </ul>
                    </div>

                    <div class="feature-campaign-btn-wrap">
                        <a href="{{route('campaign_single', $featured_campaign->slug)}}" class="btn btn-filled">{!! __('app.view_details') !!}</a>
                    </div>
                </div>


                <div class="col-sm-6 col-xs-12">
                    <div class="fature-campaign-img-wrap text-right">
                        <img src="{!! media_image_uri($featured_campaign->feature_image)->feature_image !!}" class="img-fluid" />
                    </div>
                </div>

            </div>
        </div>
    </div>

    <section class="section-bg-gradient home-categories"> <!-- explore categories -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ul>
                        @foreach($categories as $cat)
                            <li><a href="{{route('single_category', $cat->slug)}}" class="btn">{{ $cat->category_name }}</a></li>
                        @endforeach
                            <li><a href="{{route('browse_campaigns')}}" class="btn">{{ __('app.browse_campaigns') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section> <!-- #explore categories -->


    <section class="footer-campaign-stats">
        <div class="container">
            <div class="row">
                <div class="col-sm-3 col-6"><h5><span>@lang('app.total_campaigns')</span> {{$campaigns_count}}</h5> </div>
                <div class="col-sm-3 col-6"><h5><span>@lang('app.funded_campaigns')</span> {{$funded_campaigns_count}}</h5> </div>
                <div class="col-sm-3 col-6"><h5><span>@lang('app.funds_raised')</span> {!! get_amount($fund_raised_count) !!}</h5>  </div>
                <div class="col-sm-3 col-6"><h5><span>@lang('app.total_backers')</span>{{$users_count}}</h5> </div>
            </div>
        </div>
    </section>


    @if($staff_picks->count())

        <section class="home-campaign ">
            <div class="container">

                <div class="section-heading">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="section-title">@lang('app.staff_picks') </h4>
                        </div>
                        <div class="col-6 text-right">
                            <a href="{{route('projects_we_loved')}}">{{__('app.view_all')}} &rightarrow;</a>
                        </div>
                    </div>
                </div>


                <div class="row">

                    <div class="box-campaign-lists">

                        @foreach($staff_picks as $spc)
                            @php
                                $percent_raised = $spc->percent_raised();
                            @endphp

                            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 box-campaign-item">
                                <div class="box-campaign">
                                    <div class="box-campaign-content">

                                        <div class="box-campaign-img">
                                            <a href="{{route('campaign_single', $spc->slug)}}"> <img src="{{ $spc->feature_img_url()->feature_image }}" /></a>
                                        </div>

                                        <div class="box-campaign-description">
                                            <h4>
                                                <a href="{{route('campaign_single', $spc->slug)}}"> {{$spc->title}} </a>
                                            </h4>

                                            <ul>
                                                <li><img src="{!! $spc->user->get_gravatar() !!}"> {{$spc->user->name}}</li>
                                                <li>{!! $percent_raised !!}% {!! __('app.funded') !!}</li>
                                            </ul>
                                        </div>

                                        <div class="box-campaign-summery">
                                            <ul>
                                                <li><strong>{{$spc->days_left()}}</strong> @lang('app.days_left')</li>
                                                <li class="text-center"><strong>{!! get_amount($spc->success_payments->sum('amount')) !!}</strong> @lang('app.funded')</li>
                                                <li class="text-right"> <strong>{!! get_amount($spc->goal) !!}</strong> {!! __('app.goal') !!} </li>
                                            </ul>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endforeach



                    </div>  <!-- #box-campaign-lists -->


                </div>

            </div><!-- /.container -->

        </section>
    @endif




    @if($new_campaigns->count())
        <section class="home-campaign new-home-campaigns">
            <div class="container">

                <div class="section-heading">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="section-title">@lang('app.new_campaigns') </h4>
                        </div>
                        <div class="col-6 text-right">
                            <a href="{{route('browse_campaigns')}}">{{__('app.view_all')}} &rightarrow;</a>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="box-campaign-lists">
                        @foreach($new_campaigns as $nc)
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
                </div>

            </div><!-- /.container -->
        </section>
    @endif

    @if($funded_campaigns->count())
        <section class="home-campaign ">
            <div class="container">

                <div class="section-heading">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="section-title">@lang('app.recently_funded_campaigns') </h4>
                        </div>
                        <div class="col-6 text-right">
                            <a href="{{route('recently_funded_campaigns')}}">{{__('app.view_all')}} &rightarrow;</a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="box-campaign-lists">

                        @foreach($funded_campaigns as $fc)
                            @php
                                $percent_raised = $fc->percent_raised();
                            @endphp

                            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 box-campaign-item">
                                <div class="box-campaign">
                                    <div class="box-campaign-content">
                                        <div class="box-campaign-img">
                                            <a href="{{route('campaign_single', $fc->slug)}}"> <img src="{{ $fc->feature_img_url()->feature_image }}" /></a>
                                        </div>

                                        <div class="box-campaign-description">
                                            <h4>
                                                <a href="{{route('campaign_single', $fc->slug)}}"> {{$fc->title}} </a>
                                            </h4>

                                            <ul>
                                                <li><img src="{!! $fc->user->get_gravatar() !!}"> {{$fc->user->name}}</li>
                                                <li>{!! $percent_raised !!}% {!! __('app.funded') !!}</li>
                                            </ul>
                                        </div>

                                        <div class="box-campaign-summery">
                                            <ul>
                                                <li><strong>{{$fc->days_left()}}</strong> @lang('app.days_left')</li>
                                                <li class="text-center"><strong>{!! get_amount($fc->success_payments->sum('amount')) !!}</strong> @lang('app.funded')</li>
                                                <li class="text-right"> <strong>{!! get_amount($fc->goal) !!}</strong> {!! __('app.goal') !!} </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>  <!-- #box-campaign-lists -->
                </div>

            </div><!-- /.container -->
        </section>
    @endif



    <section class="why-choose-us-wrap">


        <div class="container">

            <div class="section-heading">
                <div class="row">
                    <div class="col-6">
                        <h4 class="section-title">@lang('app.why') {{get_option('site_name')}} ? </h4>
                    </div>


                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="why-choose-us-box">
                        <div class="icon">
                            <i class="fa fa-lock"></i>
                        </div>
                        <div class="title">
                            <h4>@lang('app.secure')</h4>
                        </div>
                        <div class="desc">
                            <p>@lang('app.secure_desc')</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="why-choose-us-box">
                        <div class="icon">
                            <i class="fa fa-history"></i>
                        </div>
                        <div class="title">
                            <h4>@lang('app.flexible')</h4>
                        </div>
                        <div class="desc">
                            <p>@lang('app.flexible_desc')</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="why-choose-us-box">
                        <div class="icon">
                            <i class="fa fa-thumbs-up"></i>
                        </div>
                        <div class="title">
                            <h4>@lang('app.easy')</h4>
                        </div>
                        <div class="desc">
                            <p>@lang('app.easy_desc')</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="why-choose-us-box">
                        <div class="icon">
                            <i class="fa fa-gift"></i>
                        </div>
                        <div class="title">
                            <h4>@lang('app.supports_reward')</h4>
                        </div>
                        <div class="desc">
                            <p>@lang('app.supports_reward_desc')</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </section>

    @include('footer_get_start_section')

@endsection

@section('page-js')

@endsection