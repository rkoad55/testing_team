@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')
    


                <div class="col-xs-12">
                    <h2>Origin</h2>
                    <h2 class="subtitle">Manage Network Origin of your site</h2>
 <input type="hidden" name="csrftoken" value="{{csrf_token()}}" >
    
  








<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>Origin IP Information
    
    
</h3>




<p>
Configures your site to use a different IP address for your origin server.</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $dns_check=$zoneSetting->where('name','dns_check')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','dns_check')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="dns_check" name="dns_check">
                <option {{ $dns_check == "1" ? "selected":"" }} value="1">OFF</option>
                <option {{ $dns_check == "0" ? "selected":"" }} value="0">ON</option>
                
               
                
                
            </select>
          
            <div style="padding-top:20px; {{ $dns_check == "1" ? "display: none;":"" }} " class="row ipBox">
              <form method="post" class="SettingForm" id="ipSettingForm">
              <div class="col-lg-7">
          <input class="form-control setting" name="ip"  settingid="{{$zoneSetting->where('name','ip')->first()->id }}" type="text" value="{{ $zoneSetting->where('name','ip')->first()->value }}">
        </div>

        <div class="col-lg-3"><button class="btn btn-primary" type="submit"> Save</button></div>
      </form>
      </div>
          </div>
      </div>

    </div>




<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>Custom Domains
    
    
</h3>




<p>
You must add CNAME or ANAME records for custom domains  for them to work</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $dns_check=$zoneSetting->where('name','dns_check')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>
<div   class="row">
  <form action="post" class="customDomain">
    
  
             <div class="col-lg-8">
                <input class="  form-control" type="text" name="customDomain">

                 <input class="  form-control" type="hidden" value="{{ $zone->id }}" name="zid">
             </div>
              <div  class="col-lg-4">

                <button class="btn btn-primary" type="submit"> Create </button>
                
             </div>

             </form>
           </div>

           @foreach($customDomains as $customDomain)
           <div id="record_{{ $customDomain->id }}" style="padding-top: 20px;"  class="row">
             <div class="col-lg-8">
                {{ $customDomain->custom_domain }}
             </div>
              <div  class="col-lg-4">


                <input type="button" record-id="{{ $customDomain->id }}" class="deleteCustomDomain  form-control" name="" value="Delete">
             </div>
           </div>
            
           @endforeach   
          </div>
      </div>

    </div>




<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>Custom Host Header
    
    
</h3>




<p>
Configures your site to use a different IP address for your origin server.</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $dns_check=$zoneSetting->where('name','set_host_header')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
          
          
            <div style="" class="row ipBox">
              <form method="post" class="SettingForm" id="ipSettingForm">
              <div class="col-lg-7">
          <input class="form-control setting" name="set_host_header"  settingid="{{$zoneSetting->where('name','set_host_header')->first()->id }}" type="text" value="{{ $zoneSetting->where('name','set_host_header')->first()->value }}">
        </div>

        <div class="col-lg-3">

          <button class="btn btn-primary" type="submit"> Save</button>
           </div>
      </form>
      </div>
          </div>
      </div>

    </div>


{{-- @if($zoneSetting->where('name','zoneshield')->first()) --}}
<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>Origin Shield

 
    
</h3>




<p>
 Choose the geographical location of the Origin Shield</p>

  <p class="text-info">This setting was last changed 2 days ago</p>


</div>
          
          
          </div>

          @if($zoneSetting->where('name','zoneshield')->first())
          <?php $zoneshield=$zoneSetting->where('name','zoneshield')->first()->value; 

          $shieldId=$zoneSetting->where('name','zoneshield')->first()->id;
          ?>

          @else
           <?php $zoneshield=null; $shieldId=null; ?>
          @endif
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

          @if($zoneSetting->where('name','waf')->first()->value=="disabled")
           <select  settingid="{{ $shieldId }}"  style="width: 200px;" class="select2 changeableSetting" id="zoneshield" name="zoneshield">
                <option {{ $zoneshield == "0" ? "selected":"" }} value="0">Disabled</option>
                 <option {{ $zoneshield == "ams" ? "selected":"" }} value="ams">Amsterdam</option>
                <option {{ $zoneshield == "sjc" ? "selected":"" }} value="sjc">San Jose</option>
                <option {{ $zoneshield == "vir" ? "selected":"" }} value="vir">Virginia</option>

                
                
            </select>
          @else
            <div class="alert alert-info">OriginShield can only be enabled when WAF is turned off.
</div>
          @endif
          </div>

         
      </div>

    </div>


{{-- @endif --}}















</div>

@stop

@section('javascript') 
    <script>
        window.route_mass_crud_entries_destroy = '{{ route('admin.users.mass_destroy') }}';
    </script>
@endsection
