@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app2')

@section('content')
   

<div class="row">
                <div class="col-xs-12">
                    <h2>Analytics</h2>
                    <h2 class="subtitle">View performance and security statistics for {{ $zone->name }}</h2>

                    <div class="section-title">
                        <div class="row">
                            <div class="col-xs-12 col-md-9">
                                <h3>Web Traffic</h3>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <form method="post">
 {{csrf_field()}}
                                <select class="select2 form-control " id="minutes" name="minutes">
                                    @if($zoneObj->plan=="enterprise")
                <option {{ $minutes == "30" ? "selected":"" }} value="30">Last 30 Minutes</option>
                @endif

                  @if($zoneObj->plan=="enterprise" OR $zoneObj->plan=="pro")
                <option {{ $minutes == "360" ? "selected":"" }} value="360">Last 6 Hours</option>
                <option {{ $minutes == "720" ? "selected":"" }} value="720">Last 12 Hours</option>
                    @endif
                <option {{ $minutes == "1440" ? "selected":"" }} value="1440">Last 24 Hours</option>
                <option {{ $minutes == "10080" ? "selected":"" }} value="10080">Last 7 Days</option>
                <option {{ $minutes == "43200" ? "selected":"" }} value="43200">Last Month</option>

                                </select>
                            </form>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-white">
                        <div class="tabbable full-width-tabs">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#requests" data-toggle="tab">Requests</a></li>
                                <li><a href="#bandwidth" data-toggle="tab">Bandwidth</a></li>
                                <li><a href="#visitors" data-toggle="tab">Visitors</a></li>
                                <li><a href="#threats" data-toggle="tab">Threats</a></li>
                                <li><a href="#http_statuses" data-toggle="tab">Status</a></li>
                            </ul>
                        </div>
                        <div class="panel-body">
                            <div class="tab-content">
                                <div class="tab-pane active" id="requests">
                                     <h2>Requests Served</h2>
            <div class="row">
            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Total Requests</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong><?=number_format($request_all);?></strong></p>
                </div>
            </div>
            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Cached Requests</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong><?=number_format($request_cached);?></strong></p>
            </div>
                        </div>

            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Uncached Requests</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong><?=number_format($request_uncached);?></strong></p>
            </div>
                        </div>

        </div>




          <div id="request-graph" class="graphbox" style="height: 230px;"></div>
                                </div>
                                <div class="tab-pane" id="bandwidth">
                                    <h2>Bandwidth</h2>
            <div class="row">
            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Total Bandwidth</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong>{{ FileSize::bytesToHuman($bandwidth_all) }}</strong></p>
                </div>
            </div>
            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Cached Bandwidth</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong>{{ FileSize::bytesToHuman($bandwidth_cached) }}</strong></p>
            </div>
                        </div>

            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Uncached Bandwidth</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong>{{ FileSize::bytesToHuman($bandwidth_saved) }}</strong></p>
            </div>
                        </div>

        </div>

          <div id="bandwidth-graph" class="graphbox"  style="height: 230px;">
          </div>

                                </div>
                                <div class="tab-pane" id="visitors">
                                    

            <h2>Unique Visitors</h2>
            <div class="row">
            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Total Unique Visitors</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong><?=number_format($unique_all);?></strong></p>
                </div>
            </div>
            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Maximum Unique Visitors</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong><?=number_format($unique_max);?></strong></p>
            </div>
                        </div>

            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Minimum Unique Visitors</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong><?=number_format($unique_min);?></strong></p>
            </div>
                        </div>

        </div>

             <div id="visitors-graph" class="graphbox" style="height: 230px;"></div>
                                </div>
                                <div class="tab-pane" id="threats">
                                      <h2>Threats</h2>
            <div class="row">
            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Total Threats</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong><?=number_format($threats_all);?></strong></p>
                </div>
            </div>
            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Top Country</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong><?=$top_threats_country;?></strong></p>
            </div>
                        </div>

            <div class="col-lg-4 ">
            <div class="panel">
                <p class="boxheading"><b>Top Threat Type</b></p>
                <p><?=$timestamp;?></p>
                <p class="num"><strong><?php
 
                switch ($top_threat_type) {


                    case '0':
                      
                      echo "NA";
                      break;

                    case 'bic.ban.unknown':
                        echo "Bad Browser";
                        break;
                    case 'macro.chl.captchaFail':
                        echo "Bad IP";
                        break;
                    case 'macro.chl.jschlFail':
                        echo "Human Challenged";
                        break;
                    case 'user.ban.ip':
                        echo "IP block (user)";
                        break;
                    case 'user.ban.ipr16':
                        echo "IP range block (/16)";
                        break;
                    case 'user.ban.ipr24':
                        echo "IP range block (/24)";
                        break;
                    case 0:
                        echo "NA";
                        break;
                }
                ?></strong></p>
            </div>
                        </div>

        </div>
            @if($threats_all>0)
            <div id="threats-graph" class="graphbox" style="height: 230px;"></div>
            @endif

                                </div>
                                <div class="tab-pane" id="http_statuses">
                                    <h2>Status Codes</h2>
            <div class="row">
            

        </div>
           
            <div id="statuses-graph" class="graphbox" style="height: 230px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>




                </div>
            </div>









<div class="clear-fix">&nbsp;</div>
<div class="panel panel-default">
    <div class="panel-heading"><h4>Traffic<i class="fa fa-line-chart pull-left"></i></h4>{{$timestamp}}</div>
    <div class="panel-body"><div id="traffic_container" style="max-width:100%;"></div></div>
</div>
<div class="clear-fix">&nbsp;</div>



  @if($zone->els==1)
<div class="row">

      @if(count($browserOnly)>0)
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Browsers Used</strong><br><?php echo $timestamp; ?></div>
            <div class="panel-body">
                <div id="browsers"></div>
            </div>
        </div>
    </div>
@endif

      @if(count($deviceType)>0)
  <div class="col-sm-6">
<div class="panel panel-default">
    <div class="panel-heading"><strong>Device Type</strong><br><?php echo $timestamp; ?>
       </div>
    <div class="panel-body">
        <div id="deviceType" ></div>
    </div>
</div>
</div>
@endif
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Top Browser Versions</strong><br><?php echo $timestamp; ?></div>
            <div class="panel-body">
                <table class="table table-responsive table-striped">

                    @if(count($browsers)>0)
                                    <thead>
                        <tr><th>Browser Name</th>
                        <th>Requests</th>
                    </tr></thead>
                    <tbody>
                        
                        @foreach (array_slice($browsers,0,5) as $key => $value)
                            <tr>
                                <td><a class="pointer"  data-toggle="modal" data-target="#versionDetails_{{ $key }}">{{ $key }}</a></td>
                                <td>{{ array_sum($value) }}</td>
                            </tr>
                        @endforeach
                        @else
                           <tr>No Broswers data found.</tr>
                        @endif                          
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  

    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Top Platforms</strong><br><?php echo $timestamp; ?></div>
            <div class="panel-body">
                <table class="table table-responsive table-striped">

                    @if(count($platforms)>0)
                                    <thead>
                        <tr><th>Platform Name</th>
                        <th>Requests</th>
                    </tr></thead>
                    <tbody>
                        
                        @foreach (array_slice($platforms,0,5) as $key => $value)
                            <tr>
                                <td>{{ $key }}</td>
                                <td>{{ $value }}</td>
                            </tr>
                        @endforeach
                        @else
                           <tr>No Platforms data found.</tr>
                        @endif                          
                    </tbody>
                </table>
            </div>
        </div>
    </div>

  </div>

@endif

<div class="clear-fix">&nbsp;</div>
<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Top Threats Origins</strong><br><?=$timestamp;?></div>
            <div class="panel-body">
                <table class="table table-responsive table-striped">
                <?php if ($top_threats_origin != '') { ?>
                    <thead>
                        <th>Country</th>
                        <th>Requests</th>
                    </thead>
                    <tbody>
                        <?php 
                        
                            foreach ($top_threats_origin as $key => $value) { 
                        ?>
                            <tr>
                                <td><?=$value['name']?></td>
                                <td><?=$value['value']?></td>
                            </tr>
                        <?php }
                        } else 
                        echo "<tr>No threats Found.</tr>";
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Top Traffic Origins</strong><br><?=$timestamp;?></div>
            <div class="panel-body">
                <table class="table table-responsive table-striped">
                <?php if ($top_traffic_origins != '') { ?>
                <thead>
                    <th>Country</th>
                    <th>Traffic</th>
                </thead>
                <tbody>
                    <?php 
                    
                    foreach ($top_traffic_origins as $key => $value) { ?>
                        <tr>
                            <td><?=$value['name']?></td>
                            <td><?=number_format($value['value'])?></td>
                        </tr>
                    <?php } 
                    } else 
                        echo "<tr>No traffic found.</tr>";
                    ?>
                </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Top Crawlers/Bots</strong><br><?=$timestamp;?></div>
            <div class="panel-body">
                <table class="table table-responsive table-striped">
                <?php if ($top_bots != '') {?>
                    <thead>
                        <th>Crawler/Bot</th>
                        <th>Pages Crawled</th>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($top_bots as $key => $value) { ?>
                            <tr>
                                <td><?=$key?></td>
                                <td><?=number_format($value['value'])?></td>
                            </tr>
                        <?php } 
                        } else
                            echo "<tr>No bots found.</tr>";
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="section-title">
    <div class="row">
        <div class="col-xs-12">
            <h3>Performance</h3>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Fewer Servers Needed</strong><br><?=$timestamp;?></div>
            <div class="panel-body">
             <div class="col-lg-6">
                <div  id="server_needed" data-dimension="200" data-text="<?=$servers_needed?>%"  data-width="30" data-fontsize="38" data-percent="<?=$servers_needed?>" data-fgcolor="#FF4500" data-bgcolor="#eee"></div>
                </div>
                <div style="padding-top: 74px;" class="col-lg-6">Use Page Rules to cut costs and improve performance.</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Bandwidth Saved</strong><br><?=$timestamp;?></div>
            <div class="panel-body">
                @if($bandwidth_all)
                <div class="col-lg-6">
                <div style="" id="bandwidth_served" data-dimension="200" data-text="<?=number_format((($bandwidth_cached/$bandwidth_all)*100),0) . '%'?>"  data-width="30" data-fontsize="38" data-percent="<?=($bandwidth_cached/$bandwidth_all)*100?>" data-fgcolor="#7CFC00" data-bgcolor="#eee"></div>

            </div>

                @endif
                <div style="padding-top: 74px;" class="col-lg-6">
                <strong><?=number_format($bandwidth_cached / (1024*1024), 2) . " MB saved"; ?></strong><br>
                    <?=number_format($bandwidth_all / (1024*1024), 2) . " MB total bandwidth"; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Content Type Breakdown</strong><br>
        <?php echo $timestamp; ?></div>
    <div class="panel-body">
        <div id="chart-2" style="height: 200px;"></div>
    </div>
</div>


  @if($zone->els==1)

 <div class="row customAnalytics" >




<div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Top 5 Hosts</strong><br></div>
            <div class="panel-body">
                <table class="table table-responsive table-striped">
                @if(count($hosts)>0) 
                    <thead>
                        <th>Host</th>
                        <th>Requests</th>
                    </thead>
                    <tbody>
                        
                            @foreach ($hosts as $host)  
                      
                            <tr>
                                <td>{{ $host['key'] }}</td>
                                <td>{{ $host['doc_count'] }}</td>
                            </tr>
                        @endforeach
                        @else 
                       No Hosts Found
                       @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Top 5 Referers</strong><br></div>
            <div class="panel-body">
                <table class="table table-responsive table-striped">
                @if(count($referers)>0) 
                    <thead>
                        <th>Referer</th>
                        <th>Requests</th>
                    </thead>
                    <tbody>
                        
                            @foreach ($referers as $referer)  
                      
                            <tr>
                                <td>{{ $referer['key']=="" ? "Direct Traffic" : $referer['key']  }}</td>
                                <td>{{ $referer['doc_count'] }}</td>
                            </tr>
                        @endforeach
                        @else 
                       No Hosts Found
                       @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Top 5 Ip Addreses</strong><br></div>
            <div class="panel-body">
                <table class="table table-responsive table-striped">
                @if(count($clientsIP)>0) 
                    <thead>
                        <th>Client IP</th>
                        <th>Requests</th>
                    </thead>
                    <tbody>
                        
                            @foreach ($clientsIP as $clientip)  
                      
                            <tr>
                                <td><a data-minutes="{{ $minutes }}" class="showIPDetails" href="#">{{ $clientip['key'] }}</a></td>
                                <td>{{ $clientip['doc_count'] }}</td>
                            </tr>
                        @endforeach
                        @else 
                       No Hosts Found
                       @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Most Requested URI</strong><br></div>
            <div class="panel-body">
                <table class="table table-responsive table-striped">
                @if(count($ClientRequestURI)>0) 
                    <thead>
                        <th>URI</th>
                        <th>Requests</th>
                    </thead>
                    <tbody>
                        
                            @foreach ($ClientRequestURI as $uri)  
                      
                            <tr>
                                <td>{{ $uri['key'] }}</td>
                                <td>{{ $uri['doc_count'] }}</td>
                            </tr>
                        @endforeach
                        @else 
                       No URI Found
                       @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>


<div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Most Used Request Method</strong><br></div>
            <div class="panel-body">
                <table class="table table-responsive table-striped">
                @if(count($ClientRequestMethod)>0) 
                    <thead>
                        <th>Request Method</th>
                        <th>Requests</th>
                    </thead>
                    <tbody>
                        
                            @foreach ($ClientRequestMethod as $uri)  
                      
                            <tr>
                                <td>{{ $uri['key'] }}</td>
                                <td>{{ $uri['doc_count'] }}</td>
                            </tr>
                        @endforeach
                        @else 
                       No Client Request Method Found
                       @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Top WAF Rules</strong><br></div>
            <div class="panel-body">
                <table class="table table-responsive table-striped">
                @if(count($WAFRules)>0) 
                    <thead>
                        <th>Rule</th>
                        <th>Requests</th>
                    </thead>
                    <tbody>
                        
                            @foreach (array_slice($WAFRules,0,5) as $rule)  
                                    
<?php
   //                                   $zone->wafPackage;
   // $wafRule=wafRule::where('record_id',$rule['key'])->each(function ($item, $key) {
   //      id($item->wafGroup->wafPackage->zone_id==$zone->id)
   //      {
            
   //      }
   // });
   // dd($wafRule);

   ?>
                            <tr>
                                <td><a >{{ $rule['key'] }}</a></td>
                                <td>{{ $rule['doc_count'] }}</td>
                            </tr>
                        @endforeach
                        @else 
                       No WAF Rules executed during selected time period
                       @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


<div class="modal" id="ipDetailsModal" data-reveal>

   <div class="modal-dialog modal-ip" >
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">IP Details</h4>
      </div>
      <div id="ipDetailsModalBody" class="modal-body">


</div></div>

</div>
</div>




 @foreach (array_slice($browsers,0,5) as $key => $value)

<div class="modal" id="versionDetails_{{ $key }}" data-reveal>

   <div class="modal-dialog modal-browsers" >
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">{{ $key }} Versions Breakdown</h4>
      </div>
      <div  class="modal-body">

         <table class="table table-bordered table-striped datatableVersions">
                <thead>
                    <tr>
                        
                        <th>Version</th>
                        <th>Requests</th>

                    </tr>
                </thead>
                
                <tbody>

                    @foreach($value as $version1 => $requests1)

                            <tr>
                                <td>
                                        {{ str_replace("_", ".", $version1) }}
                                </td>

                                <td>
                                        {{ $requests1 }}
                                </td>
                            </tr>   
                    @endforeach

                </tbody>

            </table>

</div>

</div>

</div>
</div>

@endforeach

<style type="text/css">
    .customAnalytics .col-sm-4 .panel-body
    {
        min-height: 340px;
    }
    .customAnalytics td
    {

        max-width: 230px;
         overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
    }

    .customAnalytics td:hover
    {
       
         overflow:auto;
  text-overflow: unset;
  white-space: unset;
       
    }
    
</style>

@endif
<div class="section-title">
    <div class="row">
        <div class="col-xs-12">
            <h3>Security</h3>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Total Threats Stopped</strong><br><?=$timestamp;?></div>
            <div class="panel-body" style="height:200px;">
                <div>
                    <h1 style="vertical-align:middle;text-align:center; margin-top:18%;"><?=$threats_all?></h1>
                </div>  
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Traffic Served Over SSL</strong><br><?=$timestamp;?></div>
            <div class="panel-body">
            <div class="col-lg-6">
                <div style="" id="ssl" data-dimension="200" data-text="<?=$ssl_graph . '%'?>"  data-width="30" data-fontsize="38" data-percent="<?=$ssl_graph?>" data-fgcolor="rgb(155, 202, 62)" data-bgcolor="#eee"></div>

                </div>
                <div style="padding-top: 74px;" class="col-lg-6">
                    <strong><?=number_format($ssl)?> SSL secured requests</strong><br>
                    <?=number_format($unencrypted_ssl); ?> unsecured requests
                </div>
            </div>
        </div>
    </div>

</div>

<div class="clear-fix">&nbsp;</div>

<div class="panel panel-default">
    <div class="panel-heading"><h4>Threat Sources<i class="fa fa-line-chart pull-left"></i></h4>{{$timestamp}}</div>
    <div class="panel-body">

<div id="countries_container" style="max-width:100%;"></div>
</div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><strong>Types of Threats Mitigated</strong><br><?=$timestamp;?></div>
    <div class="panel-body">
        <div id="chart-4" style="height: 200px;"></div>
    </div>
</div>
<script src="https://www.google.com/jsapi"></script>
<script src="{{ url('js/jquery.circliful.min.js') }}"></script>

<script src="{{ url('js/randomColor.js') }}"></script>
<script src="{{ url('js/chartkick.js') }}"></script>
   <script src="https://code.highcharts.com/maps/highmaps.js"></script>
        <script src="https://code.hisghcharts.com/maps/modules/data.js"></script>
        <script src="https://code.highcharts.com/maps/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/mapdata/custom/world.js"></script>
    <script src="{{ url('js/raphael-min.js') }}"></script>
    <script src="{{ url('morris/morris.min.js') }}"></script>

    <script src="{{ url('easypiechart/jquery.easy-pie-chart.js') }}"></script>


     
        <!-- jquery ui -->
    <script type="text/javascript">

    $(document).ready(function(){
    requests = Morris.Area({
        element: 'request-graph',
        data: <?php echo json_encode($requests); ?>,
        xkey: 'period',
        xLabels: "<?php echo $xlabel; ?>",
        ykeys: ['cached', 'uncached'],
        labels: ['Cached', 'Un-Cached'],
        lineColors: ['#12A6DA','#222D32'],
        resize: true,
        parseTime: false,
    });

  @if($zone->els==1)
total={{ $browserTotal }}
    browsers = Morris.Donut({
        element: 'browsers',
        data: <?php echo json_encode($browserOnly); ?>,
        colors:randomColor({hue: 'random',luminosity: 'dark',count: 54}),

        formatter: function (value, data) { return  Math.round((value/total *100)) + '%' ; }
       

        
       
    });
deviceTypeTotal={{ $browserTotal }}
        deviceType = Morris.Donut({
        element: 'deviceType',
        data: <?php echo json_encode($deviceType); ?>,
        colors:randomColor({hue: 'random',luminosity: 'dark',count: 54}),
         formatter: function (value, data) { return  Math.round((value/deviceTypeTotal *100)) + '%' ; }

       
       

        
       
    });
@endif

    bandwidth = Morris.Area({
        element: 'bandwidth-graph',
        data: <?php echo json_encode($bandwidth); ?>,
        xkey: 'period',
        xLabels: "<?php echo $xlabel; ?>",
        parseTime: false,
        ykeys: ['cached', 'uncached'],
        resize: true,
        labels: ['Cached (GB)', 'Un-Cached (GB)'],
        lineColors: ['#12A6DA','#222D32']
    });

    visitors=Morris.Area({
        element: 'visitors-graph',
        data: <?php echo json_encode($uniques); ?>,
        xkey: 'period',
        xLabels: "<?php echo $xlabel; ?>",
        ykeys: ['visits'],
        labels: ['Unique Visitors'],
        lineColors: ['#12A6DA','#222D32'],
        resize: true,
        parseTime: false,
    });


     statuses=Morris.Area({
        element: 'statuses-graph',
        data: <?php echo json_encode($status_codes); ?>,
        xkey: 'k_period',
        xLabels: "<?php echo $xlabel; ?>",
        ykeys: [ 'k_206', 'k_301', 'k_302', 'k_304', 'k_403','k_530','k_500'],
        labels: ['206','301','302','304','403','530','500'],
       
        resize: true,
        parseTime: false,
      
    });



 @if($threats_all>0)
     threats=Morris.Area({
        element: 'threats-graph',
        data: <?php echo json_encode($threats); ?>,
        xkey: 'period',
        xLabels: "<?php echo $xlabel; ?>",
        parseTime: false,
        ykeys: ['bad_browser', 'human_challenged', 'browser_challenged', 'ip_block_user', 'ip_range_block_16', 'ip_range_block_24'],
        labels: ['Bad Browser', 'Human Challenged', 'Browser Challenged', 'IP block (user)', 'IP range block(/16)', 'IP range block (/24)']
    });
    @endif


    new Chartkick.PieChart("chart-2", <?php echo json_encode($content_type); ?>);
     @if($zone->els==1)
    <?php // new Chartkick.PieChart("deviceType", <?php echo json_encode($deviceType); ?> <?php // ); ?>
     @endif
    new Chartkick.PieChart("chart-4", <?php echo json_encode($threats_mitigated); ?>);
    $(document).ready(function() {
        $('#server_needed').circliful();
        $('#bandwidth_served').circliful();
        $('#ssl').circliful();
    });


});







// $('a[data-toggle="tab"]').on('click',function(){


//     $(this).children(".loader:first").show();
//     alert($(this).children(".loader:first").html());

// })

$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
  var target = $(e.target).attr("href") // activated tab
  //alert($(this).children(".loader:first").css("display"));
  // $(this).children(".loader:first").show().delay(10000,function(){
  //     });
    //alert($(this).children(".loader:first").css("display"));
  //alert($(target).find('.graphbox').css('height'));
  if($(target).find('.graphbox').css('height')!="230px")
  {

    $(".loader").hide();
    return false;

  }
  
  // 


//   $('.graphbox').css('height','230px');

// bandwidth.redraw();
// visitors.redraw();
//   threats.redraw();
//   $(window).trigger('resize');

//     $('.graphbox').css('height','auto');
//     $(target).find('svg').attr('height','230');
//   return true;
  switch (target) {
    case "#requests":

      $(target).find('.graphbox').css('height','230px');
      requests.redraw();
      $(window).trigger('resize');
      $(target).find('.graphbox').css('height','auto');
       $(target).find('svg').attr('height','230');

      break;
    case "#bandwidth":

       $(target).find('.graphbox').css('height','230px');
      bandwidth.redraw();
        $(window).trigger('resize');

       $(target).find('.graphbox').css('height','auto');
       $(target).find('svg').attr('height','230');

      
      
      break;

      case "#visitors":

       $(target).find('.graphbox').css('height','230px');
      visitors.redraw();
      $(window).trigger('resize');

       $(target).find('.graphbox').css('height','auto');
       $(target).find('svg').attr('height','230');
      break;

      case "#threats":

       $(target).find('.graphbox').css('height','230px');
      threats.redraw();
      $(window).trigger('resize');

       $(target).find('.graphbox').css('height','auto');
       $(target).find('svg').attr('height','230');
      break;

       case "#http_statuses":

       $(target).find('.graphbox').css('height','230px');
      statuses.redraw();
      $(window).trigger('resize');

       $(target).find('.graphbox').css('height','auto');
       $(target).find('svg').attr('height','230');
      break;

  }


  $(".loader").hide();
});


















$(document).ready(function(){
    $(function () {

    $.getJSON('{{ route('admin.analytics.countries', ['zone' => $zoneName, 'minutes' => $minutes]) }}', function (data) {

            // Initiate the chart
            $('#countries_container').highcharts('Map', {

                title : {
                    text : 'Threat Sources'
                },

                mapNavigation: {
                    enabled: true,
                    enableDoubleClickZoomTo: true
                },

                colorAxis: {
                    minColor: '#fc564e',
                    maxColor: '#a30000',
                    type: 'linear'
                },

                series : [{
                    data : data,
                    mapData: Highcharts.maps['custom/world'],
                    joinBy: ['iso-a2', 'code'],
                    name: 'Threats',
                    states: {
                        hover: {
                            color: '#000'
                        }
                    },
                    tooltip: {
                        valueSuffix: ' threats'
                    }
                }],

                chart: {
                    style: {
                        fontFamily: "'Source Sans Pro',sans-serif"
                    }
                }
            });
        });
    });
    $(function () {
        $.getJSON('{{ route('admin.analytics.traffic', ['zone' => $zoneName, 'minutes' => $minutes]) }}', function (data) {

            // Initiate the chart
            $('#traffic_container').highcharts('Map', {

                title : {
                    text : 'Traffic'
                },

                mapNavigation: {
                    enabled: true,
                    enableDoubleClickZoomTo: true
                },

                colorAxis: {
                    min: 1,
                    max: 100000,
                    minColor: '#0cbaff',
                    maxColor: '#025bea',
                    type: 'logarithmic'
                },

                series : [{
                    data : data,
                    mapData: Highcharts.maps['custom/world'],
                    joinBy: ['iso-a2', 'code'],
                    name: 'Traffic',
                    states: {
                        hover: {
                            color: '#BADA55'
                        }
                    },
                    tooltip: {
                        valueSuffix: ' visitors'
                    }
                }],
                chart: {
                    style: {
                        fontFamily: "'Source Sans Pro',sans-serif"
                    }
                }
            });
        });
/**
 * Dark theme for Highcharts JS
 * @author Torstein Honsi
 */

// Load the fonts
Highcharts.createElement('link', {
   href: '//fonts.googleapis.com/css?family=Unica+One',
   rel: 'stylesheet',
   type: 'text/css'
}, null, document.getElementsByTagName('head')[0]);

Highcharts.theme = {
   colors: ["#2b908f", "#90ee7e", "#f45b5b", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee",
      "#55BF3B", "#DF5353", "#7798BF", "#aaeeee"],
   chart: {
      backgroundColor: {
         linearGradient: { x1: 0, y1: 0, x2: 1, y2: 1 },
         stops: [
            [0, '#2a2a2b'],
            [1, '#3e3e40']
         ]
      },
      style: {
         fontFamily: "'Unica One', sans-serif"
      },
      plotBorderColor: '#606063'
   },
   title: {
      style: {
         color: '#E0E0E3',
         textTransform: 'uppercase',
         fontSize: '20px'
      }
   },
   subtitle: {
      style: {
         color: '#E0E0E3',
         textTransform: 'uppercase'
      }
   },
   xAxis: {
      gridLineColor: '#707073',
      labels: {
         style: {
            color: '#E0E0E3'
         }
      },
      lineColor: '#707073',
      minorGridLineColor: '#505053',
      tickColor: '#707073',
      title: {
         style: {
            color: '#A0A0A3'

         }
      }
   },
   yAxis: {
      gridLineColor: '#707073',
      labels: {
         style: {
            color: '#E0E0E3'
         }
      },
      lineColor: '#707073',
      minorGridLineColor: '#505053',
      tickColor: '#707073',
      tickWidth: 1,
      title: {
         style: {
            color: '#A0A0A3'
         }
      }
   },
   tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.85)',
      style: {
         color: '#F0F0F0'
      }
   },
   plotOptions: {
      series: {
         dataLabels: {
            color: '#B0B0B3'
         },
         marker: {
            lineColor: '#333'
         }
      },
      boxplot: {
         fillColor: '#505053'
      },
      candlestick: {
         lineColor: 'white'
      },
      errorbar: {
         color: 'white'
      }
   },
   legend: {
      itemStyle: {
         color: '#E0E0E3'
      },
      itemHoverStyle: {
         color: '#FFF'
      },
      itemHiddenStyle: {
         color: '#606063'
      }
   },
   credits: {
      style: {
         color: '#666'
      }
   },
   labels: {
      style: {
         color: '#707073'
      }
   },

   drilldown: {
      activeAxisLabelStyle: {
         color: '#F0F0F3'
      },
      activeDataLabelStyle: {
         color: '#F0F0F3'
      }
   },

   navigation: {
      buttonOptions: {
         symbolStroke: '#DDDDDD',
         theme: {
            fill: '#505053'
         }
      }
   },

   // scroll charts
   rangeSelector: {
      buttonTheme: {
         fill: '#505053',
         stroke: '#000000',
         style: {
            color: '#CCC'
         },
         states: {
            hover: {
               fill: '#707073',
               stroke: '#000000',
               style: {
                  color: 'white'
               }
            },
            select: {
               fill: '#000003',
               stroke: '#000000',
               style: {
                  color: 'white'
               }
            }
         }
      },
      inputBoxBorderColor: '#505053',
      inputStyle: {
         backgroundColor: '#333',
         color: 'silver'
      },
      labelStyle: {
         color: 'silver'
      }
   },

   navigator: {
      handles: {
         backgroundColor: '#666',
         borderColor: '#AAA'
      },
      outlineColor: '#CCC',
      maskFill: 'rgba(255,255,255,0.1)',
      series: {
         color: '#7798BF',
         lineColor: '#A6C7ED'
      },
      xAxis: {
         gridLineColor: '#505053'
      }
   },

   scrollbar: {
      barBackgroundColor: '#808083',
      barBorderColor: '#808083',
      buttonArrowColor: '#CCC',
      buttonBackgroundColor: '#606063',
      buttonBorderColor: '#606063',
      rifleColor: '#FFF',
      trackBackgroundColor: '#404043',
      trackBorderColor: '#404043'
   },

   // special colors for some of the
   legendBackgroundColor: 'rgba(0, 0, 0, 0.5)',
   background2: '#505053',
   dataLabelsColor: '#B0B0B3',
   textColor: '#C0C0C0',
   contrastTextColor: '#F0F0F3',
   maskColor: 'rgba(255,255,255,0.3)'
};

// Apply the theme
Highcharts.setOptions(Highcharts.theme);
    });
/**
 * Dark theme for Highcharts JS
 * @author Torstein Honsi
 */

// Load the fonts
Highcharts.createElement('link', {
   href: '//fonts.googleapis.com/css?family=Unica+One',
   rel: 'stylesheet',
   type: 'text/css'
}, null, document.getElementsByTagName('head')[0]);

Highcharts.theme = {
   colors: ["#2b908f", "#90ee7e", "#f45b5b", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee",
      "#55BF3B", "#DF5353", "#7798BF", "#aaeeee"],
   chart: {
      backgroundColor: {
         linearGradient: { x1: 0, y1: 0, x2: 1, y2: 1 },
         stops: [
            [0, '#2a2a2b'],
            [1, '#3e3e40']
         ]
      },
      style: {
         fontFamily: "'Unica One', sans-serif"
      },
      plotBorderColor: '#606063'
   },
   title: {
      style: {
         color: '#E0E0E3',
         textTransform: 'uppercase',
         fontSize: '20px'
      }
   },
   subtitle: {
      style: {
         color: '#E0E0E3',
         textTransform: 'uppercase'
      }
   },
   xAxis: {
      gridLineColor: '#707073',
      labels: {
         style: {
            color: '#E0E0E3'
         }
      },
      lineColor: '#707073',
      minorGridLineColor: '#505053',
      tickColor: '#707073',
      title: {
         style: {
            color: '#A0A0A3'

         }
      }
   },
   yAxis: {
      gridLineColor: '#707073',
      labels: {
         style: {
            color: '#E0E0E3'
         }
      },
      lineColor: '#707073',
      minorGridLineColor: '#505053',
      tickColor: '#707073',
      tickWidth: 1,
      title: {
         style: {
            color: '#A0A0A3'
         }
      }
   },
   tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.85)',
      style: {
         color: '#F0F0F0'
      }
   },
   plotOptions: {
      series: {
         dataLabels: {
            color: '#B0B0B3'
         },
         marker: {
            lineColor: '#333'
         }
      },
      boxplot: {
         fillColor: '#505053'
      },
      candlestick: {
         lineColor: 'white'
      },
      errorbar: {
         color: 'white'
      }
   },
   legend: {
      itemStyle: {
         color: '#E0E0E3'
      },
      itemHoverStyle: {
         color: '#FFF'
      },
      itemHiddenStyle: {
         color: '#606063'
      }
   },
   credits: {
      style: {
         color: '#666'
      }
   },
   labels: {
      style: {
         color: '#707073'
      }
   },

   drilldown: {
      activeAxisLabelStyle: {
         color: '#F0F0F3'
      },
      activeDataLabelStyle: {
         color: '#F0F0F3'
      }
   },

   navigation: {
      buttonOptions: {
         symbolStroke: '#DDDDDD',
         theme: {
            fill: '#505053'
         }
      }
   },

   // scroll charts
   rangeSelector: {
      buttonTheme: {
         fill: '#505053',
         stroke: '#000000',
         style: {
            color: '#CCC'
         },
         states: {
            hover: {
               fill: '#707073',
               stroke: '#000000',
               style: {
                  color: 'white'
               }
            },
            select: {
               fill: '#000003',
               stroke: '#000000',
               style: {
                  color: 'white'
               }
            }
         }
      },
      inputBoxBorderColor: '#505053',
      inputStyle: {
         backgroundColor: '#333',
         color: 'silver'
      },
      labelStyle: {
         color: 'silver'
      }
   },

   navigator: {
      handles: {
         backgroundColor: '#666',
         borderColor: '#AAA'
      },
      outlineColor: '#CCC',
      maskFill: 'rgba(255,255,255,0.1)',
      series: {
         color: '#7798BF',
         lineColor: '#A6C7ED'
      },
      xAxis: {
         gridLineColor: '#505053'
      }
   },

   scrollbar: {
      barBackgroundColor: '#808083',
      barBorderColor: '#808083',
      buttonArrowColor: '#CCC',
      buttonBackgroundColor: '#606063',
      buttonBorderColor: '#606063',
      rifleColor: '#FFF',
      trackBackgroundColor: '#404043',
      trackBorderColor: '#404043'
   },

   // special colors for some of the
   legendBackgroundColor: 'rgba(0, 0, 0, 0.5)',
   background2: '#505053',
   dataLabelsColor: '#B0B0B3',
   textColor: '#C0C0C0',
   contrastTextColor: '#F0F0F3',
   maskColor: 'rgba(255,255,255,0.3)'
};

// Apply the theme
Highcharts.setOptions(Highcharts.theme);
});



    </script>

    


    
@stop

@section('javascript')
    <script>
        window.route_mass_crud_entries_destroy = '{{ route('admin.users.mass_destroy') }}';
    </script>
@endsection
