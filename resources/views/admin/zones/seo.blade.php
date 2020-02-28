@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')
    

<div class="row">
                <div class="col-xs-12">
                    <h2>SEO</h2>
                    <h2 class="subtitle">Manage Setting related to SEO</h2>
 <input type="hidden" name="csrftoken" value="{{csrf_token()}}" >
    
  








<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>Add Canonical Header 
    
    
</h3>




<p>
Add a header to CDN assets that tells Google where the original file is hosted. This helps your site avoid duplicate content issues.</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $canonical_link_headers=$zoneSetting->where('name','canonical_link_headers')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','canonical_link_headers')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="canonical_link_headers" name="canonical_link_headers">
                <option {{ $canonical_link_headers == "0" ? "selected":"" }} value="0">OFF</option>
                <option {{ $canonical_link_headers == "1" ? "selected":"" }} value="1">ON</option>
                
               
                
                
            </select>
          
          </div>
      </div>

    </div>




<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>Robots.txt

 
    
</h3>




<p>
Add a robots.txt file to the CDN root directory. This lets you manage crawling rules for search engines.</p>

  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $disallow_robots=$zoneSetting->where('name','disallow_robots')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','disallow_robots')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="disallow_robots" name="disallow_robots">
                <option {{ $disallow_robots == "0" ? "selected":"" }} value="0">OFF</option>
                <option {{ $disallow_robots == "1" ? "selected":"" }} value="1">ON</option>
                
                
                
            </select>

            <div style="padding-top:20px; {{ $disallow_robots == "0" ? "display: none;":"" }} " class="row ipBox">
              <form method="post" class="SettingForm" id="robotsForm">
              <div class="col-lg-7">
          <textarea class="form-control setting" name="disallow_robots_txt"  settingid="{{$zoneSetting->where('name','disallow_robots_txt')->first()->id }}" >{{ $zoneSetting->where('name','disallow_robots_txt')->first()->value }}</textarea> 
        </div>

        <div class="col-lg-3">

            <button class="btn btn-primary" type="submit"> Save</button>

        </div>
      </form>
      </div>
          
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
