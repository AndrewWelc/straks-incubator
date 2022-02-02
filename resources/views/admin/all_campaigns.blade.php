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

                    <div class="admin-campaign-lists-sub-head">
                        <div class="row">
                            <div class="col-md-5">
                                @lang('app.total') : {{$campaigns->count()}}
                            </div>

                            <div class="col-md-7">
                                <form method="get" action="{{route('campaign_admin_search')}}">
                                    <div class="form-row align-items-center float-right">
                                        <div class="col-auto">
                                            <input type="text" name="q" value="{{request('q')}}" class="form-control mb-2" placeholder="@lang('app.campaign_title_keyword')">
                                        </div>

                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-primary mb-2">@lang('app.search')</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>

                    @if($campaigns->count() > 0)
                        <table class="table table-striped table-bordered table-responsive">

                            <tr>
                                <th width="70">@lang('app.image')</th>
                                <th>@lang('app.title')</th>
                                <th>@lang('app.owner_info')</th>
                                <th>@lang('app.actions')</th>
                            </tr>

                            @foreach($campaigns as $campaign)

                                <tr>
                                    <td><img src="{{$campaign->feature_img_url()->thumbnail}}" class="img-fluid" /></td>
                                    <td>{{$campaign->title}}

                                        @if($campaign->is_funded == 1)
                                            <p class="bg-success text-white p-1">@lang('app.added_to_funded')</p>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>{{$campaign->user->name}}</strong> <br />
                                        @lang('app.address') : {{$campaign->address}}
                                    </td>

                                    <td>
                                        <a href="{{route('campaign_single', $campaign->slug)}}" class="btn btn-primary btn-sm" data-toggle="tooltip" title="View"><i class="fa fa-eye"></i> </a>

                                        @if($campaign->status == 0)
                                            <a href="{{route('campaign_status', [$campaign->id, 'approve'])}}" class="btn btn-success btn-sm" data-toggle="tooltip" title="@lang('app.approve')"><i class="fa fa-check-circle-o"></i> </a>
                                            <a href="{{route('campaign_status', [$campaign->id, 'block'])}}" class="btn btn-danger btn-sm" data-toggle="tooltip" title="@lang('app.block')"><i class="fa fa-ban"></i> </a>


                                        @elseif($campaign->status == 1)

                                            <a href="{{route('campaign_status', [$campaign->id, 'block'])}}" class="btn btn-danger btn-sm" data-toggle="tooltip" title="@lang('app.block')"><i class="fa fa-ban"></i> </a>

                                        @elseif($campaign->status == 2)
                                            <a href="{{route('campaign_status', [$campaign->id, 'approve'])}}" class="btn btn-success btn-sm" data-toggle="tooltip" title="@lang('app.approve')"><i class="fa fa-check-circle-o"></i> </a>
                                        @endif

                                        @if(request()->segment(3) == 'expired_campaigns')
                                            @if($campaign->is_funded != 1)
                                                <a href="{{route('campaign_status', [$campaign->id, 'funded'])}}" class="btn btn-info btn-sm" data-toggle="tooltip" title="@lang('app.mark_as_funded')"><i class="fa fa-check-circle-o"></i>  @lang('app.mark_as_funded')</a>
                                            @endif
                                        @endif

                                        <a href="{{route('campaign_delete', $campaign->id)}}" class="btn btn-delete btn-danger btn-sm" data-toggle="tooltip" title="@lang('app.delete')"><i class="fa fa-trash-o"></i> </a>

                                        @if($campaign->is_staff_picks != 1)
                                            <a href="{{route('campaign_status', [$campaign->id, 'add_staff_picks'])}}" class="btn btn-info btn-sm" data-toggle="tooltip" title="@lang('app.add_staff_picks')"><i class="fa fa-plus-square-o"></i>  @lang('app.add_staff_picks')</a>

                                        @else
                                            <a href="{{route('campaign_status', [$campaign->id, 'remove_staff_picks'])}}" class="btn btn-warning btn-sm" data-toggle="tooltip" title="@lang('app.remove_staff_picks')"><i class="fa fa-minus-square-o"></i>  @lang('app.remove_staff_picks')</a>
                                        @endif

                                        <a href="{{route('campaign_status', [$campaign->id, 'feature'])}}" class="btn btn-{{!$campaign->is_feature?'outline-':''}}secondary" data-toggle="tooltip" title="{{$campaign->is_feature? __('app.added_to_feature') : __('app.add_to_feature')}}">
                                            <i class="fa fa-bookmark"></i> {{$campaign->is_feature? __('app.added_to_feature') : __('app.add_to_feature')}}
                                        </a>



                                    </td>

                                </tr>

                            @endforeach

                        </table>

                        {!! $campaigns->links() !!}
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

@section('page-js')

    <script type="text/javascript">
        $(document).ready(function() {
            $('.btn-delete').click(function(e){
                if (! confirm("@lang('app.are_you_sure_undone')") ){
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection