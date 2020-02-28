@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app2')

@section('content')
    




<div class="row">
                <div class="col-xs-12">
                    <h2>Crypto</h2>
                    <h2 class="subtitle">Manage cryptography settings for your website</h2>


 <input type="hidden" name="csrftoken" value="{{csrf_token()}}" >
    
  <div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
   SSL 
    
    
</h3>

  <p>Encrypt communication to and from your website using SSL.
</p><p>
It may take up to 24 hours after the site becomes active on BlockDOS for new certificates to issue.
</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $ssl=$zoneSetting->where('name','ssl')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','ssl')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="ssl" name="ssl">
                <option {{ $ssl === "off" ? "selected":"" }} value="off">Off</option>
                <option {{ $ssl === "flexible" ? "selected":"" }} value="flexible">Flexible</option>
                <option {{ $ssl === "full" ? "selected":"" }} value="full">Full</option>
                <option {{ $ssl === "strict" ? "selected":"" }} value="strict">Strict</option>
                
            </select>
          
          </div>
      </div>

    </div>




<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Always use HTTPS     
    
</h3>




<p>
Redirect all requests with scheme “http” to “https”. This applies to all http requests to the zone.

</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $always_use_https=$zoneSetting->where('name','always_use_https')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','always_use_https')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="always_use_https" name="always_use_https">
                <option {{ $always_use_https == "0" ? "selected":"" }} value="0">Off</option>
                <option {{ $always_use_https == "1" ? "selected":"" }} value="1">ON</option>
                
                
            </select>
          
          </div>
      </div>

    </div>

 <div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Enable HSTS (Strict-Transport-Security)
    
    
</h3>




<p>
Serve HSTS headers with all HTTPS requests

</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $security_header_strict_transport_security_enabled=$zoneSetting->where('name','security_header_strict_transport_security_enabled')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','security_header_strict_transport_security_enabled')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="security_header_strict_transport_security_enabled" name="security_header_strict_transport_security_enabled">
                <option {{ $security_header_strict_transport_security_enabled == "0" ? "selected":"" }} value="0">Off</option>
                <option {{ $security_header_strict_transport_security_enabled == "1" ? "selected":"" }} value="1">ON</option>
                
                
            </select>
          
          </div>
      </div>

    </div>








<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Authenticated Origin Pulls     
    
</h3>




<p>
Authenticated Origin Pulls 
</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $tls_client_auth=$zoneSetting->where('name','tls_client_auth')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','tls_client_auth')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="tls_client_auth" name="tls_client_auth">
                <option {{ $tls_client_auth == "off" ? "selected":"" }} value="off">Off</option>
                <option {{ $tls_client_auth == "on" ? "selected":"" }} value="on">ON</option>
                
                
            </select>
          
          </div>
      </div>

    </div>








<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Require Modern TLS     
</h3>




<p>
Only use modern versions (1.2 and 1.3) of the TLS protocol. These versions use more secure ciphers, but may restrict traffic to your site from older browsers.
</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $tls_1_2_only=$zoneSetting->where('name','tls_1_2_only')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','tls_1_2_only')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="tls_1_2_only" name="tls_1_2_only">
                <option {{ $tls_1_2_only == "off" ? "selected":"" }} value="off">Off</option>
                <option {{ $tls_1_2_only == "on" ? "selected":"" }} value="on">ON</option>
                
                
            </select>
          
          </div>
      </div>

    </div>






<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Automatic HTTPS Rewrites 
</h3>




<p>
Automatic HTTPS Rewrites helps fix mixed content by changing “http” to “https” for all resources or links on your web site that can be served with HTTPS.
</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $automatic_https_rewrites=$zoneSetting->where('name','automatic_https_rewrites')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','automatic_https_rewrites')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="automatic_https_rewrites" name="automatic_https_rewrites">
                <option {{ $automatic_https_rewrites == "off" ? "selected":"" }} value="off">Off</option>
                <option {{ $automatic_https_rewrites == "on" ? "selected":"" }} value="on">ON</option>
                
                
            </select>
          
          </div>
      </div>

    </div>




@if($zoneSetting->where('name','sha1_support')->count()>0)
          <?php $sha1_support=$zoneSetting->where('name','sha1_support')->first()->value; ?>

<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Legacy Browser Support 
</h3>




<p>
Enable support for legacy user agents that do not support certificates signed with modern SHA-2 signatures. Be aware that disabling legacy support will also prevent browsers without SNI support (e.g., IE on Windows XP, Android 2.x, etc.) from connecting securely to your site.
</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>
	

          </div>


          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','sha1_support')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="sha1_support" name="sha1_support">
                <option {{ $sha1_support == "off" ? "selected":"" }} value="off">Off</option>
                <option {{ $sha1_support == "on" ? "selected":"" }} value="on">ON</option>
                
                
            </select>
          
          </div>
      </div>

    </div>

@endif

<div class="panel panel-default panel-main">
      <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Opportunistic Encryption     
    
</h3>




<p>
Opportunistic Encryption allows browsers to benefit from the improved performance of HTTP/2 by letting them know that your site is available over an encrypted connection. Browsers will continue to show “http” in the address bar, not “https”.

</p>


  <p class="text-info">This setting was last changed 2 days ago</p>


</div>

          <?php $opportunistic_encryption=$zoneSetting->where('name','opportunistic_encryption')->first()->value; ?>
          </div>
          <div class="col-lg-4 right ">
           <div  class="setting-title" >

           </div>

           
           <select  settingid="{{$zoneSetting->where('name','opportunistic_encryption')->first()->id }}"  style="width: 200px;" class="select2 changeableSetting" id="opportunistic_encryption" name="opportunistic_encryption">
                <option {{ $opportunistic_encryption == "off" ? "selected":"" }} value="off">Off</option>
                <option {{ $opportunistic_encryption == "on" ? "selected":"" }} value="on">On</option>
                
                
                
            </select>
          
          </div>
      </div>

    </div>

    
@if($zone->plan!="free")
<div class="before-panel">
       <div class="panel-body  row">
          <div class="col-lg-8">
          <div  class="setting-title" ><h3>
Custom SSL Certificate    
</h3>




  <p>You can upload SSL Certificates for your domains here.
</p>



  

</div>

          
          </div>
          <div class="col-lg-4 right ">
             <div class="setting-title">
 </div>
              <button style="width: 200px;" class="form-control btn btn-primary addNewSSL" data-toggle="modal" data-target="#ssl-edit-modal" >Add New Custom SSL</button>

          
      </div>

    
 </div>


  <div class="row">
    <div class="col-lg-12 table-responsive">
      
     <table class="table table-bordered table-striped table-condensed">

                <thead>
                    <tr>
                        
                        <th ></th>
                        <th>Hosts</th>
                        <th>Expiration</th>

                        <th style="min-width:215px;">&nbsp;</th>

                    </tr>
                </thead>

                <tbody class="pageRulesTableBody">
                    @if (count($customCertificates) > 0)
                   <?php  $n=1; ?>
                        @foreach ($customCertificates as $customCertificate)
                            <tr id="certificate_{{ $customCertificate->id }}" data-entry-id="{{ $customCertificate->id }}">

                              <td class="drag-td"><i style="display: none;" class="drag-handle glyphicon glyphicon-resize-vertical"></i><span class="sortable-number">{{ $n }}</span></td>
                              <?php $n++; ?>
                                <td>{{ $customCertificate->hosts }}
                                </td>

                                <td>{{ $customCertificate->expiration }}
                                </td>

                             
                                
                                
                                
                              
                                <td>
                                  <input disabled="" class=""  record-id="{{$customCertificate->id}}"  type="checkbox" data-onstyle="success" data-offstyle="default" {{ $customCertificate->status == "active" ? "checked" : "" }} data-toggle="toggle" data-on="<i class='fa fa-check'></i> Active" data-off="<i class='fa fa-exclamation'></i> expired">

                               
                                  
                                    <a style="margin-left: 15px;" class="deleteCustomCertificate" certificate-id="{{$customCertificate->id}}" class="btn btn-default">
                                    <i class="glyphicon glyphicon-remove"></i>
                                    </a>


                                </td>

                            </tr>
                        @endforeach
                    @else
                        <tr>
                          <td>&nbsp;</td>
                            <td colspan="3">No Custom SSL Certificates Added yet.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

    </div>
  </div>
    </div>

@endif











</div>
</div>










<div class="modal" id="ssl-edit-modal" data-reveal>

   <div class="modal-dialog modal-lg" >
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Add New SSL</h4>
      </div>
      <div class="modal-body">

  
   
    <div class="">

<form method="post" action="addSSL" class="addSSLForm">
  <input type="hidden" name="_token" value="{{csrf_token()}}" >
<p><strong>SSL Certificate:</strong>  </p>
<textarea class="form-control ssltextarea" required="" placeholder="SSL" name="ssl"  type="text"></textarea>


<p><strong>Private Key:</strong>  </p>
<textarea class="form-control ssltextarea" required="" placeholder="SSL" name="key"  type="text"></textarea>


<input type="hidden" name="zid" value="{{ $zone->id }}">
<div class="row">
  <div class="col-lg-12 text-right">
<input class="btn addSSLbtn " type="submit" value="Add SSL">
</div>
</div>
</form>

</div>



</div></div>

</div>

</div>













@stop

@section('javascript') 
    <script>
        window.route_mass_crud_entries_destroy = '{{ route('admin.users.mass_destroy') }}';
    </script>
@endsection
