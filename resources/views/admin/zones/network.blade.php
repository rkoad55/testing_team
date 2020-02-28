@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app2')

@section('content')
    


<div class="row">
                <div class="col-xs-12">
                    <h2>Network</h2>
                    <h2 class="subtitle">Manage network settings for your website.</h2>

 <input type="hidden" name="csrftoken" value="{{csrf_token()}}" >
    
  



 





<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
WebSockets</h3>




<p>
Allow WebSockets connections to your origin server.</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $websockets=$zoneSetting->where('name','websockets')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','websockets')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="websockets" name="websockets">
                <option {{ $websockets == "off" ? "selected":"" }} value="off">Off</option>
                <option {{ $websockets == "on" ? "selected":"" }} value="on">On</option>
              
                
                
            </select>
          
          </div>
      </div>

    </div>






<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Pseudo IPv4   </h3>




<p>
Adds an IPv4 header to requests when a client is using IPv6, but the server only supports IPv4.
</p>



  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $pseudo_ipv4=$zoneSetting->where('name','pseudo_ipv4')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','pseudo_ipv4')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="pseudo_ipv4" name="pseudo_ipv4">
                <option {{ $pseudo_ipv4 == "off" ? "selected":"" }} value="off">Off</option>
                <option {{ $pseudo_ipv4 == "add_header" ? "selected":"" }} value="add_header">Add Header</option>
                <option {{ $pseudo_ipv4 == "overwrite_header" ? "selected":"" }} value="overwrite_header">Overwrite Header</option>
                
                
            </select>
          
          </div>
      </div>

    </div>


<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
IP Geolocation   </h3>




<p>
Include the country code of the visitor location with all requests to your website.</p>
<p>
  Note: You must retrieve the IP Geolocation information from the CF-IPCountry HTTP header.
</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $ip_geolocation=$zoneSetting->where('name','ip_geolocation')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','ip_geolocation')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="ip_geolocation" name="ip_geolocation">
                <option {{ $ip_geolocation == "off" ? "selected":"" }} value="off">Off</option>
                <option {{ $ip_geolocation == "on" ? "selected":"" }} value="on">On</option>
               
                
                
            </select>
          
          </div>
      </div>

    </div>










</div>
</div>

@stop

@section('javascript') 
    <script>
        window.route_mass_crud_entries_destroy = '{{ route('admin.users.mass_destroy') }}';
    </script>
@endsection
