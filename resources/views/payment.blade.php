@extends('layouts.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection
@section('content')
    <section class="campaign-details-wrap">
        @include('single_campaign_header')
        <div class="container">

            <div class="row">
                <div class="col-md-12">

                    <div class="checkout-wrap">

                        <div class="contributing-to">
                            <p class="contributing-to-name"><strong> @lang('app.you_are_contributing_to') {{$campaign->user->name}}</strong></p>
                            <h3>{{$campaign->title}}</h3>
                        </div>

                        <hr />

                        <div class="row">
                             @if(get_option('enable_paypal') == 1)
                                <div class="col-md-3">
                                    {{ Form::open(['route' => 'payment_paypal_receive']) }}
                                    <input type="hidden" name="cmd" value="_xclick" />
                                    <input type="hidden" name="no_note" value="1" />
                                    <input type="hidden" name="lc" value="UK" />
                                    <input type="hidden" name="currency_code" value="{{get_option('currency_sign')}}" />
                                    <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
                                    <button type="submit" class="btn btn-info"> <i class="fa fa-paypal"></i> @lang('app.pay_with_paypal')</button>
                                    {{ Form::close() }}
                                </div>
                            @endif


                            @if(get_option('enable_straks') == 1)
                                <div class="col-md-3">
                                    <button class="btn btn-primary" id="straksTransferBtn"><i class="fa fa-straks"></i> @lang('app.pay_with_straks')</button>
                                </div>
                            @endif
                        </div>

                        @if(get_option('enable_straks') == 1)
                                @php
                                        $payment_address = get_straksaddress('straks_payment_address');
                                @endphp
                            <div class="straksPaymetWrap" style="display: none;">

                                <div class="row">
                                    <div class="col-md-8 mx-auto">


                                        <div class="alert alert-info">
                                            <h4> @lang('app.campaign_unique_info') #{{$campaign->id}} </h4>
                                        </div>

                                        <div class="jumbotron">
                                            <h4>@lang('app.straks_payment_instruction')</h4>

                                            <table class="table">
                                                <tr>
                                                    <th>@lang('app.straks_payment_address')</th>
                                                    <td>@php echo $payment_address @endphp</td>
                                                </tr>
                                                </table>
                                        </div>

                                        <div id="straksTransferStatus"></div>

                                        {{ Form::open(['route'=>'straks_transfer_submit', 'id'=>'straksTransferForm', 'class' => 'form-horizontal', 'files' => true]) }}
                                    <input type="hidden" name="straks_payment_address" value="@php echo $payment_address @endphp" />

                                        <div class="form-group {{ $errors->has('straks_transaction_hash')? 'has-error':'' }}">
                                            <label for="straks_transaction_hash" class="col-4 control-label">@lang('app.straks_transaction_hash') <span class="field-required">*</span></label>
                                            <div class="col-8">
                                                <input type="text" class="form-control" id="straks_transaction_hash" value="{{ old('straks_transaction_hash') }}" name="straks_transaction_hash" placeholder="@lang('app.straks_transaction_hash')">
                                                {!! $errors->has('straks_transaction_hash')? '<p class="help-block">'.$errors->first('straks_transaction_hash').'</p>':'' !!}
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <div class="col-offset-4 col-8">
                                                <button type="submit" class="btn btn-primary">@lang('app.pay')</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        @endif

                    </div>

                </div>

            </div>

        </div>

    </section>

@endsection

@section('page-js')

    <script>
        $(function() {
            $('.stripe-button').on('token', function(e, token){
                $('#stripeForm').replaceWith('');

                $.ajax({
                    url : '{{route('payment_stripe_receive')}}',
                    type: "POST",
                    data: { stripeToken : token.id, _token : '{{ csrf_token() }}' },
                    success : function (data) {
                        if (data.success == 1){
                            $('.checkout-wrap').html(data.response);
                            toastr.success(data.msg, '@lang('app.success')', toastr_options);
                        }
                    }
                });
            });

            @if(get_option('enable_straks') == 1)

            $('#straksTransferBtn').click(function(){
                $('.straksPaymetWrap').slideToggle();
            });

            $('#straksTransferForm').submit(function(e){
                e.preventDefault();

                var form_input = $(this).serialize()+'&_token={{csrf_token()}}';

                $.ajax({
                    url : '{{route('straks_transfer_submit')}}',
                    type: "POST",
                    data: form_input,
                    success : function (data) {
                        if (data.success == 1){
                            $('.checkout-wrap').html(data.response);
                            toastr.success(data.msg, '@lang('app.success')', toastr_options);
                        }
                    },
                    error   : function ( jqXhr, json, errorThrown ) {
                        var errors = jqXhr.responseJSON;
                        var errorsHtml= '';
                        $.each( errors, function( key, value ) {
                            errorsHtml += '<li>' + value[0] + '</li>';
                        });
                        toastr.error( errorsHtml , "Error " + jqXhr.status +': '+ errorThrown);
                    }
                });

            });
            @endif

        });
    </script>

@endsection