@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('content')
    




<div class="row">
                <div class="col-xs-12">
                    <h2>Caching</h2>
                    <h2 class="subtitle">Manage caching settings for your website</h2>


 <input type="hidden" name="csrftoken" value="{{csrf_token()}}" >
    
  <div class="panel panel-default panel-main cacheClearDiv">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Purge Cache    
</h3>




  <p>Clear cached files to force BlockDOS to fetch a fresh version of those files from your web server. You can purge files selectively or all at once.</p>
  <p>Note: Purging the cache may temporarily degrade performance for your website.</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          
          </div>
          <div class="col-lg-4 right ">
          <div  class="setting-title" >
          </div>
              <div class="row">
              
               <div class="col-lg-8 ">
               
               <button swalTitle="Purge All Cache?" swalText="Purging your cache may slow your website temporarily." zoneName="{{ $zone->name }}" action="purgeCacheAll" class="btn btn-info customActions">Purge Everything</button>
               
              </div>
              </div>

              

                 <div class="row">
              
               <div class="col-lg-8">
               
               <button swalTitle="Purge Individual Files?" swalText="You can purge up to 30 files at a time.

Note: Wildcards are not supported with single file purge at this time. You will need to specify the full path to the file.
Separate tags(s) with commas, or list one per line " zoneName="{{ $zone->name }}" action="purgeFiles" extra="files" class="btn btn-info customActions">Purge Files</button>
               
              </div>
              </div>

               <div class="row">
              
               <div class="col-lg-8">
                
                @if($zone->plan == "free") 

                <button disabled="disabled" class="btn btn-danger customActions">Purge Tags (Not Available for this domain)</button>

 @else
                <button  swalTitle="Purge Cache By Tags" swalText="You can purge up to 30 tags at a time.

Separate tags(s) with commas, or list one per line" zoneName="{{ $zone->name }}" action="purgeTags" extra="tags" class="btn btn-info customActions">Purge Tags</button>

   @endif
               
              </div>
              </div>
          </div>
      </div>

    </div>












<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Default Cache Time    
</h3>




<p>
Define how long content should remain cached on the CDN if the origin's Cache-Control header is not being used or being ignored.
</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $cache_valid=$zoneSetting->where('name','cache_valid')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','cache_valid')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="cache_valid" name="cache_valid">

               
                <option {{ $cache_valid == "1d" ? "selected":"" }} value="1d">1 Day</option>
                <option {{ $cache_valid == "7d" ? "selected":"" }} value="7d">7 Days</option>
                <option {{ $cache_valid == "1M" ? "selected":"" }} value="1M">1 Month</option>
                <option {{ $cache_valid == "12M" ? "selected":"" }} value="12M">12 Months</option>
               

                
                
            </select>
          
          </div>
      </div>

    </div>







<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Cache Control Header 
</h3>




<p>
Define how long content delivered by the CDN should remain cached by web browsers and other clients.
</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $expires=$zoneSetting->where('name','expires')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','expires')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="expires" name="expires">

               
                <option {{ $expires == "1d" ? "selected":"" }} value="1d">1 Day</option>
                <option {{ $expires == "7d" ? "selected":"" }} value="7d">7 Days</option>
                <option {{ $expires == "1M" ? "selected":"" }} value="1M">1 Month</option>
                <option {{ $expires == "12M" ? "selected":"" }} value="12M">12 Months</option>
                <option {{ $expires == "" ? "selected":"" }} value="">Respect Existing Headers</option>

                
                
            </select>
          
          </div>
      </div>

    </div>







<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Use Stale </h3>




<p>
Serve expired content while the CDN is fetching new content or when the origin is down.</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $use_stale=$zoneSetting->where('name','use_stale')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','use_stale')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="use_stale" name="use_stale">
                <option {{ $use_stale == "0" ? "selected":"" }} value="0">Off</option>
                <option {{ $use_stale == "1" ? "selected":"" }} value="1">On</option>
                
                
            </select>
          
          </div>
      </div>

    </div>







<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Query String </h3>




<p>
Treat files with query string parameters such as ?q=foo as separate cacheable files. This is often enabled to force the CDN to re-cache files when they're updated.

  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $queries=$zoneSetting->where('name','queries')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','queries')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="queries" name="queries">
                <option {{ $queries == "0" ? "selected":"" }} value="0">Off</option>
                <option {{ $queries == "1" ? "selected":"" }} value="1">On</option>
                
                
            </select>
          
          </div>
      </div>

    </div>




<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Ignore Cache Control 
 </h3>




<p>
Ignore any TTL and Expiry headers set on the origin server. Instead use the "Set Default Cache Time" setting above.
  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $ignore_cache_control=$zoneSetting->where('name','ignore_cache_control')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','ignore_cache_control')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="ignore_cache_control" name="ignore_cache_control">
                <option {{ $ignore_cache_control == "0" ? "selected":"" }} value="0">Off</option>
                <option {{ $ignore_cache_control == "1" ? "selected":"" }} value="1">On</option>
                
                
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
