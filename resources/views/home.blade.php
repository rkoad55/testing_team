@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')
    

<div class="container">



<div class="row home">
<div class="col-lg-10">

 @if(auth()->user()->id==1)


               <div class="panel panel-inverse panel-main">
    <div class="panel-heading"><h2>Dashboard</h2></div>
    <div class="panel-body"><h3></h3>
        <table class="table table-bordered">
            <tbody>

                Users : {{ \App\User::count()-1 }} <br>
                Zones : {{ \App\Zone::count() }} <br>
                Cloudflare Accounts : {{ \App\Cfaccount::count() }} <br>

               
                
                
                        
                    
            </tbody>
        </table>
    </div>
</div>
                @else
           
                <div class="panel panel-inverse panel-main">
    <div class="panel-heading"><h2>Select Zone</h2></div>
    <div class="panel-body"><h3></h3>
        <table class="table table-bordered">
            <tbody>

           
            <?php

            $user=\App\User::find(auth()->user()->id);
             $allowedZone =  $request->session()->get('zone', null);
            ?>


            
            @if($allowedZone!=null)
            
                <tr>
                    <td> <a href="{{$allowedZone}}/overview">{{ $allowedZone }}</a></td>
                </tr>
            
            @elseif($user->owner!=1 AND \App\User::find($user->owner)->isNotAn('reseller'))
                
                @foreach(\App\Zone::where('user_id',$user->owner)->get() as $zone)
                <tr>
                    <td> <a href="{{$zone->name}}/overview">{{ $zone->name }}</a></td>
                </tr>
            @endforeach

            @else

            
            <?php $ids=\App\User::where('owner',$user->id)->pluck('id')->toArray();
            $ids[]=$user->id;

             ?>
            @foreach(\App\Zone::whereIn('user_id',$ids)->get() as $zone)
                <tr>
                    <td> <a href="{{$zone->name}}/overview">{{ $zone->name }}</a></td>
                </tr>
            @endforeach
                
            @endif
                
                        
                    
            </tbody>
        </table>
    </div>
</div>

 @endif
</div>
</div>
</div>
@stop


