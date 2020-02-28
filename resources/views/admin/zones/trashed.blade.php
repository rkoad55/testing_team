@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')
    <h3 class="page-title">Soft Deleted
     @if($type=="sp") 
     SP
     @else
     Cloudflare
     @endif Domains/Zones</h3>
    <p>



        
    </p>

    <div class="panel panel-default">
        <div class="panel-heading">
            @lang('global.app_list')
        </div>

     
        <div class="panel-body table-responsive">
            <table class="table table-bordered table-striped {{ count($zones) > 0 ? 'datatable' : '' }}">
                <thead>
                    <tr>
                        <th style="text-align:center;"><input type="checkbox" id="select-all" /></th>

                        <th>Zone TLD</th>
                        <th>Owner</th>
                        <th>Associated 
                            @if($type=="sp") 
                            SP
                            @else
                        CloudFlare
                        @endif
                         Account</th>
                        <th>&nbsp;</th>

                    </tr>
                </thead>
                
                <tbody>

                    @if(count($zones) > 0)

                     
                        @foreach ($zones as $zone)

                        @if(($type=="sp" AND $zone->cfaccount_id==0) OR ($type=="cf" AND $zone->cfaccount_id!=0))
                            <tr data-entry-id="{{ $zone->id }}">
                                <td>{{ $zone->id }}</td>

                                <td><a href="{{ $zone->name }}/overview">{{ $zone->name }}</a></td>
                                @if($zone->user)
                                <td><a href="{{ route('admin.users.zones',[$zone->user->id]) }}" class="btn btn-xs btn-info">{{ $zone->user->name }} ({{ $zone->user->email }})</a></td>
                                @else
                                    <td>{{$zone->user_id}}</td>
                                    @endif
                                <td>   
                                    

                                    


                                    @if($zone->cfaccount_id!=0)
                                        @if($zone->cfaccount->email)
                                        {{ $zone->cfaccount->email }}  
                                        @else
                                        NA
                                        @endif
                                    
                                    @else
                                    @if($zone->spaccount)
                                        {{ $zone->spaccount->alias }}
                                        @else 
                                        NA
                                        @endif
                                    @endif
                                    
                                </td>
                                <td>
                                   
                                    {!! Form::open(array(
                                        'style' => 'display: inline-block;',
                                        'method' => 'DELETE',
                                        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
                                        'route' => ['admin.zones.destroy', $zone->id])) !!}
                                    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
                                    {!! Form::close() !!}

                                    {!! Form::open(array(
                                        'style' => 'display: inline-block;',
                                        'method' => 'patch',
                                        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
                                        'route' => ['admin.zones.restore', $zone->id])) !!}
                                        <input type="hidden" name="id" value="{{ $zone->id }}" > 
                                    {!! Form::submit('Restore', array('class' => 'btn btn-xs btn-info')) !!}
                                    {!! Form::close() !!}
                                </td>

                            </tr>

                            @endif
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
    <script>
        window.route_mass_crud_entries_destroy = '{{ route('admin.users.mass_destroy') }}';
    </script>
@endsection
