@extends('layouts.app')

@section('content')

    <section class="auth-form">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mx-auto">

                    <h1 class="auth-form-title">@lang('app.login') <small>or <a href="{{route('register')}}">@lang('app.register')</a></small>  </h1>

                    <div class="auth-form-wrap">

                        @include('admin.flash_msg')


                        @if(get_option('enable_social_login') == 1)
                            <div class="row">

                                @if(get_option('enable_facebook_login') == 1)
                                    <div class="col-4">
                                        <a href="{{ route('facebook_redirect') }}" class="btn btn-lg btn-block btn-facebook">
                                            <i class="fa fa-facebook-square"></i> Facebook
                                        </a>
                                    </div>
                                @endif

                                @if(get_option('enable_google_login') == 1)
                                    <div class="col-4">
                                        <a href="{{ route('google_redirect') }}" class="btn btn-lg btn-block btn-google">
                                            <i class="fa fa-google-plus-square"></i> Google+
                                        </a>
                                    </div>
                                @endif

                                @if(get_option('enable_twitter_login') == 1)
                                    <div class="col-4">
                                        <a href="{{ route('twitter_redirect') }}" class="btn btn-lg btn-block btn-twitter">
                                            <span class="hidden-xs"><i class="fa fa-twitter"></i> Twitter</span>
                                        </a>
                                    </div>
                                @endif

                            </div>
                            <hr />
                        @endif

                        <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                            {{ csrf_field() }}

                            <div class="form-group row {{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">@lang('app.email_address')</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row {{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="password" class="col-md-4 control-label">@lang('app.password')</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password" required>

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            @if(get_option('enable_recaptcha_login') == 1)
                                <div class="form-group row  {{ $errors->has('g-recaptcha-response') ? ' has-error' : '' }}">
                                    <div class="col-md-6 offset-md-4">
                                        <div class="g-recaptcha" data-sitekey="{{get_option('recaptcha_site_key')}}"></div>
                                        @if ($errors->has('g-recaptcha-response'))
                                            <span class="help-block text-danger">
                                                <strong>{{ str_replace('g-recaptcha-response', 'reCAPTCHA', $errors->first('g-recaptcha-response')) }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="form-group row ">
                                <div class="col-md-6 offset-md-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> @lang('app.remember_me')
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row ">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        @lang('app.login')
                                    </button>

                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        @lang('app.forgot_your_password')
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page-js')
    @if(get_option('enable_recaptcha_login') == 1)
        <script src='https://www.google.com/recaptcha/api.js'></script>
    @endif
@endsection