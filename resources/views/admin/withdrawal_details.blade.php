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
                                <h1 class="page-header"> {{ $title }} </h1>
                            </div> <!-- /.col-lg-12 -->
                        </div> <!-- /.row -->
                    @endif

                    @include('admin.flash_msg')


                    <table class="table table-bordered table-striped">

                        <tr>
                            <th>@lang('app.requested_by')</th>
                            <td>{{$withdraw_request->user->name}}</td>
                        </tr>
                        <tr>
                            <th>@lang('app.campaign')</th>
                            <td> @if($withdraw_request->campaign) {{$withdraw_request->campaign->title}} @endif</td>
                        </tr>
                        <tr>
                            <th>@lang('app.method')</th>
                            <td>{{get_amount($withdraw_request->withdrawal_amount)}}</td>
                        </tr>

                        <tr>
                            <th>@lang('app.date')</th>
                            <td>{{$withdraw_request->created_at->format('jS F, Y')}}</td>
                        </tr>
                        <tr>
                            <th>@lang('app.status')</th>
                            <td>{{$withdraw_request->status}}</td>
                        </tr>
                    </table>

                    <table class="table table-bordered table-striped">

                        @if($withdraw_request->withdrawal_account == 'paypal')
                            <tr>
                                <th>@lang('app.paypal_email')</th>
                                <td>{{$withdraw_request->paypal_email}}</td>
                            </tr>
                        @elseif($withdraw_request->withdrawal_account == 'bank')
                            <tr>
                                <th>@lang('app.bank_account_holders_name')</th>
                                <td>{{$withdraw_request->bank_account_holders_name}}</td>
                            </tr>
                            <tr>
                                <th>@lang('app.bank_account_number')</th>
                                <td>{{$withdraw_request->bank_account_number}}</td>
                            </tr>
                            <tr>
                                <th>@lang('app.swift_code')</th>
                                <td>{{$withdraw_request->swift_code}}</td>
                            </tr>
                            <tr>
                                <th>@lang('app.bank_name_full')</th>
                                <td>{{$withdraw_request->bank_name_full}}</td>
                            </tr>
                            <tr>
                                <th>@lang('app.bank_branch_name')</th>
                                <td>{{$withdraw_request->bank_branch_name}}</td>
                            </tr>
                            <tr>
                                <th>@lang('app.bank_branch_city')</th>
                                <td>{{$withdraw_request->bank_branch_city}}</td>
                            </tr>
                            <tr>
                                <th>@lang('app.bank_branch_address')</th>
                                <td>{{$withdraw_request->bank_branch_address}}</td>
                            </tr>
                            <tr>
                                <th>@lang('app.country')</th>
                                <td>{{$withdraw_request->country->name}}</td>
                            </tr>

                        @endif

                    </table>

                    @if(Auth::user()->is_admin())

                        @if($withdraw_request->status == 'pending')
                            <table>
                                <tr>
                                    <td>{!! Form::open() !!}
                                        <input type="hidden" name="type" value="approved">
                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check-circle-o"></i> @lang('app.approve')</button>
                                        {!! Form::close() !!}
                                    </td>
                                    <td>
                                        {!! Form::open() !!}
                                        <input type="hidden" name="type" value="declined">
                                        <button type="submit" class="btn btn-danger"> <i class="fa fa-ban"></i> @lang('app.decline')</button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            </table>
                        @elseif($withdraw_request->status == 'approved')
                            {!! Form::open() !!}
                            <input type="hidden" name="type" value="declined">
                            <button type="submit" class="btn btn-danger"> <i class="fa fa-ban"></i> @lang('app.decline')</button>
                            {!! Form::close() !!}
                        @elseif($withdraw_request->status == 'declined')
                            {!! Form::open() !!}
                            <input type="hidden" name="type" value="approved">
                            <button type="submit" class="btn btn-success"> <i class="fa fa-check-circle-o"></i> @lang('app.approve')</button>
                            {!! Form::close() !!}
                        @endif
                    @endif

                        <div class="clearfix"></div>
                </div>

            </div>
        </div>
    </div>

@endsection