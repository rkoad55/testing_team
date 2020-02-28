@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')
    <h3 class="page-title">All
     @if($type=="sp") 
     SP
     @else
     Cloudflare
     @endif Domains/Zones</h3>
    <p>

 @if($type=="sp") 

 @if($user->branding AND $user->spZoneCount<$user->branding->sp)
 <a href="{{ route('admin.zones.spcreate') }}" class="btn btn-success">Add New SP Domain</a>
@elseif($user->branding AND $user->spZoneCount>=$user->branding->sp)
<a class="btn btn-success maxDomains">Add New SP Domain</a>
@endif
                           
                           @if(auth()->user()->id==1)
                           <a href="{{ route('admin.zones.spcreate') }}" class="btn btn-success">Add New SP Domain</a>
                             <a href="{{ route('admin.zones.trash') }}?type=sp" class="btn btn-danger">View Soft Deleted Domains</a>
                           @endif
                            @else
            
        

 @if($user->branding AND $user->cfZoneCount<$user->branding->cf)
 <a href="{{ route('admin.zones.create') }}" class="btn btn-success">Add New Cloudflare Domain</a>
 @elseif($user->branding AND $user->cfZoneCount>=$user->branding->cf)
 <a class="btn btn-success maxDomains">Add New Cloudflare Domain</a>
@endif

       
        @if(auth()->user()->id==1)
         <a href="{{ route('admin.zones.create') }}" class="btn btn-success">Add New Cloudflare Domain</a>

                             <a href="{{ route('admin.zones.trash') }}" class="btn btn-danger">View Soft Deleted Domains</a>
                           @endif

                        @endif

        
    </p>

    <div class="panel panel-default">
        <div class="panel-heading">
            @lang('global.app_list')
        </div>

     
        <div class="panel-body table-responsive">
            <table class="table table-bordered table-striped {{ count($users) > 0 ? 'datatable' : '' }}">
                <thead>
                    <tr>
                        
                        <th>Zone TLD</th>
                        <th>Owner</th>

                        @if(auth()->user()->id==1)
                        <th>Associated 
                            @if($type=="sp") 
                            SP
                            @else
                        CloudFlare
                        @endif
                         Account</th>
                         @endif
                        <th>&nbsp;</th>

                    </tr>
                </thead>
                
                <tbody>

                    @if(count($users) > 0)

                        @foreach ($users as $user1)

                         @if (count($user1->zone) > 0)
                        @foreach ($user1->zone as $zone)

                        @if(($type=="sp" AND $zone->cfaccount_id==0) OR ($type=="cf" AND $zone->cfaccount_id!=0))
                            <tr data-entry-id="{{ $zone->id }}">
                                

                                <td><a href="{{ $zone->name }}/overview">{{ $zone->name }}</a></td>
                                <td><a href="{{ route('admin.users.zones',[$zone->user->id]) }}" class="btn btn-xs btn-info">{{ $zone->user->name }} ({{ $zone->user->email }})</a>

                                    <a href="{{ route('admin.zones.ownership',[$zone->id]) }}" class="btn btn-xs btn-primary">Change Ownership</a>

                                </td>
                               @if(auth()->user()->id==1)
                                <td>   
                                    @if($zone->cfaccount_id!=0)
                                        
                                        {{ $zone->cfaccount->email }}    
                                    
                                    @else
                                    
                                        {{ $zone->spaccount->alias }}
                                    @endif
                                    
                                </td>
                                @endif
                                <td>
                                   
                                    {!! Form::open(array(
                                        'style' => 'display: inline-block;',
                                        'method' => 'DELETE',
                                        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
                                        'route' => ['admin.zones.destroy', $zone->id])) !!}
                                    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
                                    {!! Form::close() !!}
                                </td>

                            </tr>

                            @endif
                             
                        @endforeach
                        @endif

                        @foreach(\App\User::where('owner',$user1->id)->with('zone')->get() as $user)
                    @if (count($user->zone) > 0)
                        @foreach ($user->zone as $zone)

                        @if(($type=="sp" AND $zone->cfaccount_id==0) OR ($type=="cf" AND $zone->cfaccount_id!=0))
                            <tr data-entry-id="{{ $zone->id }}">
                                

                                <td><a href="{{ $zone->name }}/overview">{{ $zone->name }}</a></td>
                                <td><a href="{{ route('admin.users.zones',[$zone->user->id]) }}" class="btn btn-xs btn-info">{{ $zone->user->name }} ({{ $zone->user->email }})</a>

                                    <a href="{{ route('admin.zones.ownership',[$zone->id]) }}" class="btn btn-xs btn-primary">Change Ownership</a>

                                </td>
                               @if(auth()->user()->id==1)
                                <td>   
                                    @if($zone->cfaccount_id!=0)
                                        
                                        {{ $zone->cfaccount->email }}    
                                    
                                    @else
                                    
                                        {{ $zone->spaccount->alias }}
                                    @endif
                                    
                                </td>
                                @endif
                                <td>
                                   
                                    {!! Form::open(array(
                                        'style' => 'display: inline-block;',
                                        'method' => 'DELETE',
                                        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
                                        'route' => ['admin.zones.destroy', $zone->id])) !!}
                                    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
                                    {!! Form::close() !!}
                                </td>

                            </tr>

                            @endif
                             
                        @endforeach
                        @endif
                         @endforeach
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9">@lang('global.app_no_entries_in_table')</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('javascript') 

@endsection
