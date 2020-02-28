@inject('request', 'Illuminate\Http\Request')
<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    @include('partials.javascripts')
</head>


<body class="hold-transition skin-blue sidebar-mini">



@include('partials.topbar')

 @if(isset($zone))
 @if(!Request::is('*/home') AND !Request::is('*/zones*') AND !Request::is('*/els*') AND !Request::is('*/spels*') AND !Request::is('*/panel_logs*') AND !Request::is('*/spaccounts*') AND !Request::is('*/cfaccounts*'))


@include('partials.topmenu')

@endif
@endif

@include('partials.sidebar')
<!-- Content Wrapper. Contains page content -->
    <div class="main">
        <div class="container">
            @if(isset($siteTitle))
                <h3 class="page-title">
                    {{ $siteTitle }}
                </h3>
            @endif

            <div class="row">
                <div class="col-md-10">

                    @if (Session::has('message'))
                        <div class="note note-info">
                            <p>{!! Session::get('message') !!}</p>
                        </div>
                    @endif


                    @if (Session::has('status'))
                        <div style="margin-top: 50px" class="alert alert-info">
                            <p>{!! Session::get('status') !!}</p>
                        </div>
                    @endif

                    @if (Session::has('error'))
                        <div style="margin-top: 50px" class="alert alert-danger">
                            <p>{!! Session::get('error') !!}</p>
                        </div>
                    @endif


                    @if ($errors->count() > 0)
                        <div class="note note-danger">
                            <ul class="list-unstyled">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')

                </div>
            </div>
        </div>
    </div>


{!! Form::open(['route' => 'auth.logout', 'style' => 'display:none;', 'id' => 'logout']) !!}

{!! Form::close() !!}

    <footer class="footer">
        <div class="container">
            <p>Copyright &copy; 2020 - BlockDOS. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>