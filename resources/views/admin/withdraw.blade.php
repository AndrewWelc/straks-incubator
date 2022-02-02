@extends('layouts.admin.app')

@section('title') @if(! empty($title)) {!!$title!!} @endif - @parent @endsection

@section('content')


    <div class="dashboard-wrap">
        <div class="container">
            <div id="wrapper">

                @include('admin.menu')

                <div id="page-wrapper">

                    @if( ! empty($title))
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"> {!! $title !!}

                                    <a href="{!!route('withdrawal_preference')!!}" class="btn btn-primary pull-right"><i class="fa fa-money"></i> @lang('app.withdrawal_preference')</a>
                                </h1>

                            </div> <!-- /.col-lg-12 -->
                        </div> <!-- /.row -->
                    @endif

                    @include('admin.flash_msg')

                    @if($campaigns->count() > 0)

                        <table class="table table-bordered table-striped">
                            <tr>
                                <th>@lang('app.campaign_title')</th>
                                <th>@lang('app.raised')</th>
                                <th>@lang('app.your_commission')</th>
                                <th>@lang('app.action')</th>
                            </tr>

                            @foreach($campaigns as $campaign)
                                @php
                                    $campaign_raised = $campaign->amount_raised();
                                @endphp
                                <tr>
                                    <td> <a href="{!!route('campaign_single', $campaign->slug)!!}" target="_blank"> {!!$campaign->title!!} </a> </td>
                                    <td>{!!get_amount($campaign_raised->amount_raised)!!}</td>
                                    <td>{!!get_amount($campaign_raised->campaign_owner_commission)!!} ({!!$campaign->campaign_owner_commission!!}%)</td>

                                    <td>
                                        @if($campaign->is_ended())
                                            @if($campaign->requested_withdrawal)
                                                {!!$campaign->requested_withdrawal->status!!}
                                            @else
                                                <a href="javascript:;" class="btn btn-info btn-xs" data-toggle="modal" data-target="#{!!$campaign->id!!}-withdraw-modal">
                                                    @lang('app.withdraw')
                                                </a>
                                            @endif
                                        @else
                                            @lang('app.running')
                                        @endif

                                    </td>
                                </tr>
                            @endforeach
                        </table>

                    @else
                        <div class="no-data-wrap text-center p-5 mt-5">
                            <i class="fa fa-frown-o"></i>
                            <h1>@lang('app.no_available_data')</h1>
                        </div>
                    @endif

                    @if($withdrawal_requests->count() > 0)
                        <h3>@lang('app.all_withdrawals')</h3>

                        <table class="table table-bordered table-striped">

                            <tr>
                                <th>@lang('app.campaign')</th>
                                <th>@lang('app.method')</th>
                                <th>@lang('app.amount')</th>
                                <th>@lang('app.status')</th>
                                <th>@lang('app.date')</th>
                                <th>#</th>
                            </tr>


                            @foreach($withdrawal_requests as $withdraw)

                                <tr>
                                    <td> @if($withdraw->campaign) {!!$withdraw->campaign->title !!} @endif</td>
                                    <td>{!!$withdraw->withdrawal_account!!}</td>
                                    <td>{!!get_amount($withdraw->withdrawal_amount)!!}</td>
                                    <td>{!!$withdraw->status!!}</td>
                                    <td>{!!$withdraw->created_at->format('jS F, Y')!!}</td>
                                    <td><a href="{!!route('withdraw_request_view', $withdraw->id)!!}" class="btn-link"><i class="fa fa-eye"></i> </a> </td>
                                </tr>
                            @endforeach


                        </table>
                    @endif

                <!-- Withdrawal Modal -->

                    @foreach($campaigns as $campaign)

                        <div class="modal fade" id="{!!$campaign->id!!}-withdraw-modal" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            @lang('app.your_withdraw_amount') <strong>{!!get_amount($campaign->amount_raised()->campaign_owner_commission)!!}</strong>
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">

                                        <div class="modal-body">

                                            <h4>@lang('app.default_withdrawal_account'): <strong>{!!withdrawal_preference()!!}</strong>   <a href="{!!route('withdrawal_preference')!!}" class="btn xs btn-link">@lang('app.update') </a> </h4>

                                            <h5>@lang('app.withdrawal_method_details')</h5>


                                            <table class="table table-bordered table-striped">

                                                @if(withdrawal_preference() == 'paypal')
                                                    <tr>
                                                        <th>@lang('app.paypal_email')</th>
                                                        <td>{!!withdrawal_preference('paypal_email')!!}</td>
                                                    </tr>
                                                @elseif(withdrawal_preference() == 'bank')
                                                    <tr>
                                                        <th>@lang('app.bank_account_holders_name')</th>
                                                        <td>{!!withdrawal_preference('bank_account_holders_name')!!}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>@lang('app.bank_account_number')</th>
                                                        <td>{!!withdrawal_preference('bank_account_number')!!}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>@lang('app.swift_code')</th>
                                                        <td>{!!withdrawal_preference('swift_code')!!}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>@lang('app.bank_name_full')</th>
                                                        <td>{!!withdrawal_preference('bank_name_full')!!}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>@lang('app.bank_branch_name')</th>
                                                        <td>{!!withdrawal_preference('bank_branch_name')!!}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>@lang('app.bank_branch_city')</th>
                                                        <td>{!!withdrawal_preference('bank_branch_city')!!}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>@lang('app.bank_branch_address')</th>
                                                        <td>{!!withdrawal_preference('bank_branch_address')!!}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>@lang('app.country')</th>
                                                        <td>{!!withdrawal_preference('country_name')!!}</td>
                                                    </tr>

                                                @endif

                                            </table>

                                        </div>


                                    </div>
                                    <div class="modal-footer">

                                        {!! Form::open() !!}

                                        <input type="hidden" name="withdrawal_campaign_id" value="{!!$campaign->id!!}" />
                                        <button type="submit" class="btn btn-primary">@lang('app.submit_request')</button>
                                        {!! Form::close() !!}

                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div><!-- #End Withdrawal Modal -->

                    @endforeach

                    <div class="clearfix"></div>

                </div>

            </div>
        </div>
    </div>


@endsection