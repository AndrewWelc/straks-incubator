@extends('layouts.admin.app')

@section('title') @if(! empty($title)) {{$title}} @endif - @parent @endsection

@section('content')


    <div class="dashboard-wrap">
        <div class="container">
            <div id="wrapper">

                @include('admin.menu')

                <div id="page-wrapper">

                    @if( ! empty($title))
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"> {{ $title }}  </h1>
                            </div> <!-- /.col-lg-12 -->
                        </div> <!-- /.row -->
                    @endif

                    @include('admin.flash_msg')

                    @if($my_campaigns->count() > 0)
                        <table class="table table-striped table-bordered">

                            @foreach($my_campaigns as $campaign)
                                <tr>
                                    <td width="70">
                                        <img src="{{$campaign->feature_img_url()->thumbnail}}" class="img-fluid" />

                                        {!! $campaign->statusContext() !!}
                                    </td>
                                    <td>{{$campaign->title}}</td>

                                    <td>
                                        @if( ! $campaign->is_ended())
                                            <a href="{{route('edit_campaign', $campaign->id)}}" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i> </a>
                                        @endif

                                        <a href="{{route('campaign_single', $campaign->slug)}}" class="btn btn-success btn-sm"><i class="fa fa-eye"></i> </a>
                                    </td>

                                </tr>
                            @endforeach

                        </table>

                    @else

                        <div class="no-data-wrap text-center p-5 mt-5">
                            <i class="fa fa-frown-o"></i>
                            <h1>@lang('app.no_campaigns_to_display')</h1>
                        </div>

                    @endif


                    <div class="clearfix"></div>

                </div>

            </div>
        </div>
    </div>

@endsection
