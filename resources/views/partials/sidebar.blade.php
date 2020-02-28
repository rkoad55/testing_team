@inject('request', 'Illuminate\Http\Request')
<!-- Left side column. contains the sidebar -->
@can('users_manage')

<style type="text/css">
    
    .main{
        margin-left: 230px;

    }
    .menu
    {
        margin-left: 230px;
    }

</style>


<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
	
        <ul class="sidebar-menu">

            <li class="{{ $request->segment(1) == 'home' ? 'active' : '' }}">
                <a href="{{ url('/') }}">
                    <i class="fa fa-wrench"></i>
                    <span class="title">@lang('global.app_dashboard') </span>
                </a>
            </li>
            
            @can("users_manage")
            
            <li class="{{ $request->segment(2) == 'users' ? 'active' : '' }}">
                <a href="{{ route('admin.users.index') }}">
                    <i class="fa fa-users"></i>
                    <span class="title">Users</span>
                    <span class="pull-right-container">
                       
                    </span>
                </a>
                
            </li>
        
        @endcan


            {{-- <ul class="treeview-menu">

                    <li class="{{ $request->segment(2) == 'abilities' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.abilities.index') }}">
                            <i class="fa fa-briefcase"></i>
                            <span class="title">
                                @lang('global.abilities.title')
                            </span>
                        </a>
                    </li>
                    <li class="{{ $request->segment(2) == 'roles' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.roles.index') }}">
                            <i class="fa fa-briefcase"></i>
                            <span class="title">
                                @lang('global.roles.title')
                            </span>
                        </a>
                    </li>
                    <li class="{{ $request->segment(2) == 'users' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.users.index') }}">
                            <i class="fa fa-user"></i>
                            <span class="title">
                                @lang('global.users.title')
                            </span>
                        </a>
                    </li>

                    

                </ul>


                 --}}
            @can("resellers_manage")
             <li class="{{ $request->segment(2) == 'resellers' ? 'active' : '' }}">
                <a href="{{ route('admin.listResellers') }}">
                    <i class="fa fa-users"></i>
                    <span class="title">Resellers</span>
                    <span class="pull-right-container">
                       
                    </span>
                </a>
                
            </li>
            @endcan
            <li class="{{ ($request->segment(2) == 'zones'  AND $request->input('type')!='sp') ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.zones.index') }}">
                            <i class="fa fa-user"></i>
                            <span class="title">
                               All CF Zones
                            </span>
                        </a>
                </li>

                <li class="{{ ($request->segment(2) == 'zones' AND $request->input('type')=='sp') ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.zones.index') }}?type=sp">
                            <i class="fa fa-user"></i>
                            <span class="title">
                               All SP Zones
                            </span>
                        </a>
                </li>

                @can("resellers_manage")
                 <li class="{{ $request->segment(2) == 'cfaccounts' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.cfaccounts.index') }}">
                            <i class="fa fa-user"></i>
                            <span class="title">
                               Cloudflare Accounts
                            </span>
                        </a>
                </li>


                 <li class="{{ $request->segment(2) == 'spaccounts' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.spaccounts.index') }}">
                            <i class="fa fa-user"></i>
                            <span class="title">
                               SP Accounts
                            </span>
                        </a>
                </li>
                @endcan
                @if(auth()->user()->id==1)
                <li class="{{ $request->segment(2) == 'els' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.els') }}">
                            <i class="fa fa-user"></i>
                            <span class="title">
                               CF Logs Analysis
                            </span>
                        </a>
                </li>

                <li class="{{ $request->segment(2) == 'spels' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.spels') }}">
                            <i class="fa fa-user"></i>
                            <span class="title">
                              SP Logs Analysis
                            </span>
                        </a>
                </li>

                <li class="{{ $request->segment(2) == 'panel_logs' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.panelLogs') }}">
                            <i class="fa fa-user"></i>
                            <span class="title">
                               Panel Logs
                            </span>
                        </a>
                </li>

                @endif
                 @if(auth()->user()->id==1)
                <li class="{{ $request->segment(2) == 'packages' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.packages.index') }}">
                            <i class="fa fa-user"></i>
                            <span class="title">
                               Packages
                            </span>
                        </a>
                </li>
                @endif
                @if(auth()->user()->id!=1)
                <li class="{{ $request->segment(2) == 'branding' ? 'active active-sub' : '' }}">
                        <a href="{{ route('admin.branding') }}">
                            <i class="fa fa-user"></i>
                            <span class="title">
                               Branding
                            </span>
                        </a>
                </li>
                @endif

                @can("users_manage")
            
            <li class="{{ $request->segment(2) == 'token' ? 'active' : '' }}">
                <a href="{{ route('admin.token') }}">
                    <i class="fa fa-users"></i>
                    <span class="title">API Tokens</span>
                    <span class="pull-right-container">
                       
                    </span>
                </a>
                
            </li>
        
        @endcan


            <li class="{{ $request->segment(1) == 'change_password' ? 'active' : '' }}">
                <a href="{{ route('auth.change_password') }}">
                    <i class="fa fa-key"></i>
                    <span class="title">Change password</span>
                </a>
            </li>

            <li>
                <a href="{{ url('logout') }}">
                    <i class="fa fa-arrow-left"></i>
                    <span class="title">@lang('global.app_logout')</span>
                </a>
            </li>
        </ul>
    </section>
</aside>

 @endcan