@extends('layouts.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection

@section('meta-data')
    <meta name="description" content="{{$campaign->short_description ? $campaign->short_description : $campaign->description}}" />

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="{{$campaign->short_description ? $campaign->short_description : $campaign->description}}">
    {{--<meta name="twitter:site" content="@publisher_handle">--}}
    <meta name="twitter:title" content="@if( ! empty($title)) {{ $title }} @endif">
    <meta name="twitter:description" content="{{$campaign->short_description ? $campaign->short_description : $campaign->description}}">
    {{--<meta name="twitter:creator" content="@author_handle" />--}}
    <!-- Twitter Summary card images must be at least 120x120px -->
    <meta name="twitter:image" content="{!! $campaign->feature_img_url()->feature_image !!}">

    <!-- Open Graph data -->
    <meta property="og:title" content="@if( ! empty($title)) {{ $title }} @endif" />
    <meta property="og:url" content="{{route('campaign_single', $campaign->slug)}}" />
    <meta property="og:image" content="{{$campaign->feature_img_url()->feature_image}}" />
    <meta property="og:type" content="article" />
    <meta property="og:description" content="{{$campaign->short_description ? $campaign->short_description : $campaign->description}}" />

@endsection

@section('content')

    <section class="campaign-details-wrap">

        <div class="page-header section-bg-gradient">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>{{$campaign->title}}</h1>

                        <nav aria-label="breadcrumb" class="hu-breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}"><i class="fa fa-home"></i> @lang('app.home')</a></li>
                                <li class="breadcrumb-item"><a href="{{route('browse_campaigns')}}">@lang('app.campaigns')</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $campaign->title }}</li>
                            </ol>
                        </nav>

                    </div>
                </div>
            </div>
        </div>


        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    @include('admin.flash_msg')
                    @include('single_campaign_header')


                    <div class="campaign-decription">


                        <div class="single-campaign-embeded">


                            @if( ! empty($campaign->video))
                                <?php
                                $video_url = $campaign->video;
                                if (strpos($video_url, 'youtube') > 0) {
                                    preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $video_url, $matches);
                                    if ( ! empty($matches[1])){
                                        echo '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="https://www.youtube.com/embed/'.$matches[1].'" frameborder="0" allowfullscreen></iframe></div>';
                                    }

                                } elseif (strpos($video_url, 'vimeo') > 0) {
                                    if (preg_match('%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $video_url, $regs)) {
                                        if (!empty($regs[3])){
                                            echo '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="https://player.vimeo.com/video/'.$regs[3].'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>';
                                        }
                                    }
                                }
                                ?>

                            @else
                                <div class="campaign-feature-img">
                                    <img src="{{$campaign->feature_img_url()->original}}" class="img-fluid" />
                                </div>
                            @endif

                        </div>

                        {!! safe_output($campaign->description) !!}
                    </div>




                    @if(get_option('enable_comments') == 1)
                        <hr />
                        @php $comments = \App\Comment::approved()->parent()->whereCampaignId($campaign->id)->with('childs_approved')->orderBy('id', 'desc')->get();
                        $comments_count = \App\Comment::approved()->whereCampaignId($campaign->id)->count();
                        @endphp
                        <div class="comments-container">
                            @if($comments_count < 1)
                                <h2>@lang('app.no_comment_found')</h2>
                            @elseif($comments_count == 1)
                                <h2>{{$comments_count}} @lang('app.comment_found')</h2>
                            @elseif($comments_count > 1)
                                <h2>{{$comments_count}} @lang('app.comments_found')</h2>
                            @endif

                            <div class="post-comments-form">
                                {{ Form::open(['route'=> ['post_comments', $campaign->id], 'class' => 'form-horizontal']) }}

                                @if( ! auth()->check())
                                    <div class="form-group row {{ $errors->has('author_name')? 'has-error':'' }}">
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="author_name" value="@if(auth()->check() ) {{auth()->user()->name}}@else{{old('author_name')}}@endif" name="author_name" placeholder="@lang('app.author_name')">
                                            {!! $errors->has('author_name')? '<p class="help-block">'.$errors->first('author_name').'</p>':'' !!}
                                        </div>
                                    </div>

                                    <div class="form-group row {{ $errors->has('author_email')? 'has-error':'' }}">
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="author_email" value="@if(auth()->check() ) {{auth()->user()->email}}@else{{old('author_email')}}@endif" name="author_email" placeholder="@lang('app.author_email')">
                                            {!! $errors->has('author_email')? '<p class="help-block">'.$errors->first('author_email').'</p>':'' !!}
                                            <p class="text-info">@lang('app.email_secured')</p>
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group row {{ $errors->has('comment')? 'has-error':'' }}">
                                    <div class="col-sm-8">
                                        <textarea class="form-control" name="comment" rows="8" placeholder="@lang('app.write_your_comment')"></textarea>
                                        {!! $errors->has('comment')? '<p class="help-block">'.$errors->first('comment').'</p>':'' !!}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-8">
                                        <input type="hidden" value="" class="comment_id" name="comment_id">
                                        <button type="submit" class="btn btn-success" name="post_comment"><i class="fa fa-pencil-square"></i> @lang('app.post_comment') </button>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>

                            @if($comments->count())
                                <ul id="comments-list" class="comments-list">

                                    @foreach($comments as $comment)
                                        <li id="comment-{{$comment->id}}">
                                            <div class="comment-main-level">
                                                <!-- Avatar -->
                                                <div class="comment-avatar">
                                                    @if($comment->user_id)
                                                        <img src="{{$comment->author->get_gravatar()}}" alt="{{$comment->author_name}}">
                                                    @else
                                                        <img src="{{avatar_by_email($comment->author_email)}}" alt="{{$comment->author_name}}">
                                                    @endif
                                                </div>
                                                <!-- Contenedor del Comentario -->
                                                <div class="comment-box" data-comment-id="{{$comment->id}}">
                                                    <div class="comment-head">
                                                        <h6 class="comment-name by-author">{{$comment->author_name}}</h6>
                                                        <span>{{$comment->created_at->diffForHumans()}}</span>
                                                        <i class="fa fa-reply"></i>
                                                    </div>
                                                    <div class="comment-content">
                                                        {!! safe_output(nl2br($comment->comment)) !!}
                                                    </div>

                                                    <div class="reply_form_box" style="display: none;"></div>
                                                </div>
                                            </div>

                                        @if($comment->childs_approved)
                                            @foreach($comment->childs_approved as $childComment)
                                                <!-- Respuestas de los comentarios -->
                                                    <ul class="comments-list reply-list">
                                                        <li id="comment-{{$childComment->id}}">
                                                            <!-- Avatar -->
                                                            <div class="comment-avatar">
                                                                @if($childComment->user_id)
                                                                    <img src="{{$childComment->author->get_gravatar()}}" alt="{{$childComment->author_name}}">
                                                                @else
                                                                    <img src="{{avatar_by_email($childComment->author_email)}}" alt="{{$childComment->author_name}}">
                                                                @endif
                                                            </div>
                                                            <!-- Contenedor del Comentario -->
                                                            <div class="comment-box" data-comment-id="{{$comment->id}}">
                                                                <div class="comment-head">
                                                                    <h6 class="comment-name by-author">{{$childComment->author_name}}</h6>
                                                                    <span>{{$childComment->created_at->diffForHumans()}}</span>
                                                                    <i class="fa fa-reply"></i>
                                                                </div>
                                                                <div class="comment-content">
                                                                    {!! safe_output(nl2br($childComment->comment)) !!}
                                                                </div>
                                                                <div class="reply_form_box" style="display: none;"></div>
                                                            </div>
                                                        </li>

                                                    </ul>

                                                @endforeach
                                            @endif
                                        </li>

                                    @endforeach


                                </ul>

                            @else

                            @endif


                        </div>
                    @endif

                </div>

                <div class="col-md-4">
                    @include('campaign_single_sidebar')
                </div>

            </div>
        </div>

    </section>

@endsection

