@extends('layouts.admin.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection

@section('page-css')
    <link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap.css') }}" rel="stylesheet" />
@stop

@section('content')



    <div class="dashboard-wrap">
        <div class="container">
            <div id="wrapper">

                @include('admin.menu')

                <div id="page-wrapper">
                    @if( ! empty($title))
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"> {{ $title }}</h1>
                            </div> <!-- /.col-lg-12 -->
                        </div> <!-- /.row -->
                    @endif

                    @include('admin.flash_msg')

                    <div class="row">
                        <div class="col-md-12">

                            <table class="table table-bordered table-striped" id="jDataTable" style="width: 100%">
                                <thead>
                                <tr>
                                    <th>@lang('app.author_name')</th>
                                    <th>@lang('app.comment')</th>
                                    <th>@lang('app.created_at')</th>
                                    <th>@lang('app.actions')</th>
                                </tr>
                                </thead>
                            </table>

                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>   <!-- /#page-wrapper -->

            </div>   <!-- /#wrapper -->


        </div> <!-- /#container -->
    </div> <!-- /#dashboard wrap -->

@endsection

@section('page-js')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#jDataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin_comments_data') }}',
                aoColumns: [
                    { mData: 'author_name' },
                    { mData: 'comment' },
                    { mData: 'created_at' },
                    { mData: 'actions' },
                ],

                "aaSorting": []
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('body').on('click', '.comment_action', function (e) {
                e.preventDefault();

                var $that = $(this);
                if ($that.attr('data-action') === 'trash'){
                    if (!confirm("<?php echo trans('app.are_you_sure') ?>")) {
                        return false;
                    }
                }

                var action = $that.data('action');

                $.ajax({
                    type: 'POST',
                    url: '{{ route('comment_action') }}',
                    data: {comment_id: $that.data('id'), action: action, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        if (data.success == 1) {

                            if (action == 'approve'){
                                $that.remove();
                            }else {
                                $that.closest('tr').remove();
                            }


                            var options = {closeButton: true};
                            toastr.success(data.msg, '<?php echo trans('app.success') ?>', options)
                        }
                    }
                });
            });
        });
    </script>

    <script>
        var options = {closeButton : true};
        @if(session('success'))
            toastr.success('{{ session('success') }}', '<?php echo trans('app.success') ?>', options);
        @endif
    </script>
@endsection


