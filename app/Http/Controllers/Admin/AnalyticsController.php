<?php

namespace App\Http\Controllers\Admin;

use App\Analytics;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Zone;
use Carbon\Carbon;
use Session;

use App\wafEvent;

use App\wafRule;
use App\ElsAnalytics;
use App\Jobs\FetchAnalytics;
class AnalyticsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



    public function spAnalytics($zone, Request $request)
    {
        // return fetchAndSave();

        // die("sd");

        $zone =   Zone::where('name',$zone)->first();

        if($request->input('minutes') !==null)
        {
            $minutes=$request->input('minutes');
        }
        else
        {
            $minutes=43200;    
            // $minutes=259200;    
        }


         switch ($minutes) {
            case 1440:
                $timestamp = 'Last 24 Hours';
                $period="hourly";
                $limit=24;
                 $xlabel= 'hour';
                 $tsFormat='Y-m-d H';
                break;
             case 10080:
                $timestamp = 'Last 7 Days';
                $period="daily";
                $limit=7;
                 $xlabel= 'day';
                  $tsFormat='Y-m-d';
                break;
             case 43200:
                $timestamp = 'Last Month';
                $period="daily";
                $limit=30;
                 $xlabel= 'day';
                  $tsFormat='Y-m-d';
                break;

            case 259200:
                $timestamp = 'Last 6 Months';
                $period="daily";
                $limit=180;
                 $xlabel= 'day';
                  $tsFormat='Y-m-d';
            break;
            
            default:
                $timestamp = 'Last 24 Hours';
                 $xlabel= 'hour';
                 $period="hourly";
                  $tsFormat='Y-m-d H';
                break;
        }



        $latest=$zone->SpAnalytics->sortByDesc('timestamp')->first();
        if($latest)
        {
            $latest= $latest->timestamp;
        }
        else
        {
            $latest= Carbon::now()->format('Y-m-d H:i:s');
        }
        $ts = Carbon::createFromFormat('Y-m-d H:i:s',$latest)->subMinutes($minutes)->format('Y-m-d H:i:s');
        // dd($ts);
        //
        ////->where('timestamp',">=",$timestamp)
        ///
        
        $dataCollection=$zone->SpAnalytics->sortByDesc('timestamp');
        $statsCollection=$dataCollection->where('type','stats')->where('period',$period)->where('timestamp','>',$ts);
        $stats = $statsCollection->sortBy('timestamp')->all();

        $request_all = $statsCollection->sum("hit");
        $request_cached = $statsCollection->sum("cache_hit");
        $request_uncached = $statsCollection->sum("noncache_hit");
        


        $bandwidth = $statsCollection->sum("size");

         $threats_all = $statsCollection->sum("blocked");

        $bandwidth=number_format($bandwidth / (1024 * 1024 * 1024) , 2);


        $status_codes = $dataCollection->where('type','statuscodes')->where('period',$period)->where('timestamp','>',$ts)->sortByDesc('timestamp')->groupBy('timestamp')->all();

        // dd($status_codes);
        $filetypes = $dataCollection->where('type','filetypes')->where('period',$period)->groupBy('timestamp')->where('timestamp','>',$ts)->sortBy('timestamp')->all();
        
        $parsed_status=[];
        foreach (array_reverse($status_codes) as $key=>$status_period) {
            # code...
            $parsed_status[$key]['timestamp']=dateFormatting($status_period[0]['timestamp'],$xlabel);
            foreach ($status_period as $status) {
                # code...
                if(starts_with($status['status_code'],'2'))
                {
                    $parsed_status[$key]['2xx']=$status['hit'];
                }elseif(starts_with($status['status_code'],'3'))
                {
                    $parsed_status[$key]['3xx']=$status['hit'];
                }elseif(starts_with($status['status_code'],'4'))
                {
                    $parsed_status[$key]['4xx']=$status['hit'];
                }elseif(starts_with($status['status_code'],'5'))
                {
                    $parsed_status[$key]['5xx']=$status['hit'];
                }
                

            }
        }


$parsed_filetypes=[];


    
      

        foreach ($filetypes as $key=>$status_period) {
            # code...
            $parsed_filetypes[$key]['timestamp']=dateFormatting($status_period[0]['timestamp'],$xlabel);
            foreach ($status_period as $filetype) {
                # code...
               
                
                $parsed_filetypes[$key][$filetype['file_type']]=$filetype['hit'];
            }
        }

       // $stats_json=array();
        foreach ($stats as $stat) {
            # code...

            $stat['period']=dateFormatting($stat['timestamp'],$xlabel);
            $stat['cached']=$stat['cache_hit'];
            $stat['uncached']=$stat['noncache_hit'];
            $stat['size']= number_format($stat['size'] / (1024 * 1024 * 1024) , 2)."GB";

           unset($stat['id']);
           unset($stat['zone_id']);
           unset($stat['created_at']);
           unset($stat['updated_at']);
           unset($stat['type']);
           unset($stat['cache_hit']);
           unset($stat['noncache_hit']);
           unset($stat['timestamp']);
        }

        $stats=array_values($stats);
        $status_codes=array_values($parsed_status);

       // echo json_encode($parsed_filetypes);
       // die();


        

        $latest=$zone->wafEvent->sortByDesc('ts')->first();
        if($latest)
        {
            $latest= $latest->ts;

        }
        else
        {
            $latest= Carbon::now()->timestamp;
        }
         // dd($minutes);

        // $latest= Carbon::now()->timestamp;
        $tsEvent = Carbon::createFromTimestamp((int)$latest)->subMinutes($minutes)->timestamp;

        //$tsEvent = Carbon::now('UTC')->subMinutes(14444)->timestamp;
    // echo($latest);

    // echo($tsEvent);
         $events = $zone->wafEvent->sortByDesc('ts')->where('ts','>',$tsEvent)->sortBy('ts')->groupBy(function($date) use($tsFormat) {
            return Carbon::createFromTimestamp($date->ts)->format($tsFormat);
});;
        
        $threats=array();
         foreach ($events as $key=>$event) {
             # code...
             
             // dd($event);
            $threats[$key]['period']=dateFormatting(Carbon::createFromTimestamp($event->first()['ts'])->toDateString(),$xlabel);
            $threats[$key]['blocked']=$event->sum('blocked');
            // $threats[$key]['SRE'] = $event->where('scope','SRE')->count();
         }
         $threats=array_values($threats);
        return ['0' => 'admin.spanalytics.index', '1' =>compact('zone','stats','minutes','timestamp','request_all','request_uncached','request_cached','bandwidth','status_codes','threats_all','threats')];
    }


    public function spLogs($zone, Request $request)
    {
// dd();
  $zone =  $zoneObj = Zone::where('name',$zone)->first();
  if($request->input('start') !==null)
        {

            $correction=0;
            if(Session::has('current_time_zone'))
            {
                $correction=Session::get('current_time_zone');
            }
            $start=Carbon::parse($request->input('start'),'UTC')->timestamp-$correction;
            
            $end=Carbon::parse($request->input('end'),'UTC')->timestamp-$correction;

            // dd(dateFormatting(Carbon::now('UTC'),"logsAnalysis"));
// dd($end);

            $convert=true;
        }
        else
        {
            $end=Carbon::now('UTC')->timestamp;   
            $start=Carbon::now('UTC')->subMinutes(1440)->timestamp;  

            $convert=true;

        }

// dd($start);

        $hosts = [
    'http://elasticsearch:ONiNeVB5NRDNo&F9@CgJAi7d@148.251.176.73:9201'       // HTTP Basic Authentication
   
];


// curl -XPUT http://<es node>:9200/.kibana/index-pattern/cloudflare -d '{"title" : "cloudflare",  "timeFieldName": "EdgeStartTimestamp"}'
$client = \Elasticsearch\ClientBuilder::create()
                    ->setHosts($hosts)->build();
  


 $body='{
   "_source": ["time","method","scheme","query_string","hostname","user_agent","client_country","client_ip","uri","cache_status","status"],
    "size":10,
    "query": {
    "bool": {
      "must": [
        {
          "match_all": {}
        },
        {
          "range": {
            "time": {
              "gte": '.$start.',
              "lte": '.$end.',
              "format": "epoch_second"
            }
          }
        }
      ]
    }
    },
    "sort": [
    {
      "time": {
        "order": "asc"
      }
    }
]
    
}';

$params = [
    'index' => 'sp_687080',
    'type' => 'doc',
    'body' => $body
];


                    $results = $client->search($params);  

$requests=false;
if(isset($results['hits']['hits']))
{
    $requests=$results['hits']['hits'];
}

// dd($results);
      


 // \App\User::find(1)->allow(['splogs']);
        // die("sd");
      
       
        return view('admin.zones.spLogs', compact('zone', 'zoneSetting','requests','start','end','convert'));
    }

    public function index($zone, Request $request)
    {
        // die();
        
        $zone =  $zoneObj = Zone::where('name',$zone)->first();

        $analytics = new FetchAnalytics($zone); 


        // return print_r($analytics);
        // return       

       

        if($zone->cfaccount_id==0)
        {
            $spAnalytics=$this->spAnalytics($zone->name,$request);
            return view($spAnalytics[0],$spAnalytics[1]);
            die();
        }


// FetchAnalytics::dispatch($zone)->onConnection("sync");
if($request->input('minutes') !==null)
{
    $minutes=$request->input('minutes');
}
else
{
    $minutes=1440;    
}


 switch ($minutes) {
            case 1440:
                $timestamp = 'Last 24 Hours';
                $xlabel= 'hour';
                break;
             case 10080:
                $timestamp = 'Last 7 Days';
                $xlabel= 'day';
                break;
             case 43200:
                $timestamp = 'Last Month';
                $xlabel= 'day';
                break;
            case 720:
                $timestamp = 'Last 12 Hours';
                $xlabel= '30m';
                break;
            case 360:
                $timestamp = 'Last 6 Hours';
                $xlabel= '15m';
                break;
            case 30:
                $timestamp = 'Last 30 Minutes';
                $xlabel= 'minute';
                break;
            
            default:
                $timestamp = 'Last 24 Hours';
                $xlabel= 'hour';
                break;
        }


try {
    $analytics->fetchAndSave($minutes);
     $results=unserialize($zone->analytics->where('minutes',$minutes)->first()->value);
     // return print_r($results->timeseries);

} catch (\Exception $e) {
   return view('admin.analytics.error',compact('zoneObj','minutes'));
}



$request_cahced1=array();

foreach ($results->timeseries as $key => $value) {
            $unqiue_array[] = $value->uniques->all;
            $bandwidth_cached_uncached[$key]['period'] = dateFormatting(date("Y-m-d H:i:s", strtotime($value->since)),$xlabel);
            $bandwidth_cached_uncached[$key]['cached'] = number_format($value->bandwidth->cached / (1024 * 1024 * 1024) , 2);
            $bandwidth_cached_uncached[$key]['uncached'] = number_format($value->bandwidth->uncached / (1024 * 1024 * 1024 ), 2);



            $request_cahced1[$key]['x'] = "new Date(".date("Y, m, d, H, i, s", strtotime($value->since)).")";
             $request_cahced1[$key]['y'] =$value->requests->cached;

             $request_uncahced1[$key]['x'] = "new Date(".date("Y, m, d, H, i, s", strtotime($value->since)).")";
             $request_uncahced1[$key]['y'] =$value->requests->uncached;

            $request_cached_uncached[$key]['period'] = dateFormatting(date("Y-m-d H:i:s", strtotime($value->since)),$xlabel);
            $request_cached_uncached[$key]['cached'] = $value->requests->cached;

            $request_cached_uncached[$key]['uncached'] = $value->requests->uncached;

            $unique_visitors[$key]['period'] = dateFormatting(date("Y-m-d H:i:s", strtotime($value->since)),$xlabel);
            $unique_visitors[$key]['visits'] = $value->uniques->all;

            $threats[$key]['period'] = dateFormatting(date("Y-m-d H:i:s", strtotime($value->since)),$xlabel);
            
            //dd();
            $status_codes[$key] =json_decode(json_encode($value->requests->http_status),true);
            $status_codes[$key]['period'] = dateFormatting(date("Y-m-d H:i:s", strtotime($value->since)),$xlabel);
            // //$status_codes=[$key]
            // dd($status_codes);
            $type = $value->threats->type;
            $a = (object)$type;
            if(isset($a->{'bic.ban.unknown'})) {
                $threats[$key]['bad_browser'] = $a->{'bic.ban.unknown'};
                $threats_mitigated[$key] = $a->{'bic.ban.unknown'};
            }
            else
                $threats[$key]['bad_browser'] = 0;
            if (isset($a->{'macro.chl.captchaFail'})) {
                $threats[$key]['human_challenged'] = $a->{'macro.chl.captchaFail'};
                $threats_mitigated[$key] = $a->{'macro.chl.captchaFail'};
            }
            else
                $threats[$key]['human_challenged'] = 0;
            if (isset($a->{'macro.chl.jschlFail'}))
                $threats[$key]['browser_challenged'] = $a->{'macro.chl.jschlFail'};
            else
                $threats[$key]['browser_challenged'] = 0;
            if (isset($a->{'user.ban.ip'}))
                $threats[$key]['ip_block_user'] = $a->{'user.ban.ip'};
            else
                $threats[$key]['ip_block_user'] = 0;
            if (isset($a->{'user.ban.ipr16'}))
                $threats[$key]['ip_range_block_16'] = $a->{'user.ban.ipr16'};
            else
                $threats[$key]['ip_range_block_16'] = 0;
            if (isset($a->{'user.ban.ipr24'}))
                $threats[$key]['ip_range_block_24'] = $a->{'user.ban.ipr24'};
            else
                $threats[$key]['ip_range_block_24'] = 0;
        }

        //dd($threats);
       //   $json=json_encode($request_uncahced1);

       // echo str_replace(')","y":','),y: ',str_replace('{"x":"new', '{x: new', $json));
       //  die();


       //  $json=json_encode($request_cahced1);

       // echo str_replace(')","y":','),y: ',str_replace('{"x":"new', '{x: new', $json));
       //  die();
        $request_all = $results->totals->requests->all;
        $request_cached = $results->totals->requests->cached;
        $request_uncached = $results->totals->requests->uncached;
        $bandwidth_all = $results->totals->bandwidth->all;
        $bandwidth_cached = $results->totals->bandwidth->cached;
        $bandwidth_uncached = $results->totals->bandwidth->uncached;
        $names = json_decode('{"BD": "Bangladesh", "BE": "Belgium", "BF": "Burkina Faso", "BG": "Bulgaria", "BA": "Bosnia and Herzegovina", "BB": "Barbados", "WF": "Wallis and Futuna", "BL": "Saint Barthelemy", "BM": "Bermuda", "BN": "Brunei", "BO": "Bolivia", "BH": "Bahrain", "BI": "Burundi", "BJ": "Benin", "BT": "Bhutan", "JM": "Jamaica", "BV": "Bouvet Island", "BW": "Botswana", "WS": "Samoa", "BQ": "Bonaire, Saint Eustatius and Saba ", "BR": "Brazil", "BS": "Bahamas", "JE": "Jersey", "BY": "Belarus", "BZ": "Belize", "RU": "Russia", "RW": "Rwanda", "RS": "Serbia", "TL": "East Timor", "RE": "Reunion", "TM": "Turkmenistan", "TJ": "Tajikistan", "RO": "Romania", "TK": "Tokelau", "GW": "Guinea-Bissau", "GU": "Guam", "GT": "Guatemala", "GS": "South Georgia and the South Sandwich Islands", "GR": "Greece", "GQ": "Equatorial Guinea", "GP": "Guadeloupe", "JP": "Japan", "GY": "Guyana", "GG": "Guernsey", "GF": "French Guiana", "GE": "Georgia", "GD": "Grenada", "GB": "United Kingdom", "GA": "Gabon", "SV": "El Salvador", "GN": "Guinea", "GM": "Gambia", "GL": "Greenland", "GI": "Gibraltar", "GH": "Ghana", "OM": "Oman", "TN": "Tunisia", "JO": "Jordan", "HR": "Croatia", "HT": "Haiti", "HU": "Hungary", "HK": "Hong Kong", "HN": "Honduras", "HM": "Heard Island and McDonald Islands", "VE": "Venezuela", "PR": "Puerto Rico", "PS": "Palestinian Territory", "PW": "Palau", "PT": "Portugal", "SJ": "Svalbard and Jan Mayen", "PY": "Paraguay", "IQ": "Iraq", "PA": "Panama", "PF": "French Polynesia", "PG": "Papua New Guinea", "PE": "Peru", "PK": "Pakistan", "PH": "Philippines", "PN": "Pitcairn", "PL": "Poland", "PM": "Saint Pierre and Miquelon", "ZM": "Zambia", "EH": "Western Sahara", "EE": "Estonia", "EG": "Egypt", "ZA": "South Africa", "EC": "Ecuador", "IT": "Italy", "VN": "Vietnam", "SB": "Solomon Islands", "ET": "Ethiopia", "SO": "Somalia", "ZW": "Zimbabwe", "SA": "Saudi Arabia", "ES": "Spain", "ER": "Eritrea", "ME": "Montenegro", "MD": "Moldova", "MG": "Madagascar", "MF": "Saint Martin", "MA": "Morocco", "MC": "Monaco", "UZ": "Uzbekistan", "MM": "Myanmar", "ML": "Mali", "MO": "Macao", "MN": "Mongolia", "MH": "Marshall Islands", "MK": "Macedonia", "MU": "Mauritius", "MT": "Malta", "MW": "Malawi", "MV": "Maldives", "MQ": "Martinique", "MP": "Northern Mariana Islands", "MS": "Montserrat", "MR": "Mauritania", "IM": "Isle of Man", "UG": "Uganda", "TZ": "Tanzania", "MY": "Malaysia", "MX": "Mexico", "IL": "Israel", "FR": "France", "IO": "British Indian Ocean Territory", "SH": "Saint Helena", "FI": "Finland", "FJ": "Fiji", "FK": "Falkland Islands", "FM": "Micronesia", "FO": "Faroe Islands", "NI": "Nicaragua", "NL": "Netherlands", "NO": "Norway", "NA": "Namibia", "VU": "Vanuatu", "NC": "New Caledonia", "NE": "Niger", "NF": "Norfolk Island", "NG": "Nigeria", "NZ": "New Zealand", "NP": "Nepal", "NR": "Nauru", "NU": "Niue", "CK": "Cook Islands", "XK": "Kosovo", "CI": "Ivory Coast", "CH": "Switzerland", "CO": "Colombia", "CN": "China", "CM": "Cameroon", "CL": "Chile", "CC": "Cocos Islands", "CA": "Canada", "CG": "Republic of the Congo", "CF": "Central African Republic", "CD": "Democratic Republic of the Congo", "CZ": "Czech Republic", "CY": "Cyprus", "CX": "Christmas Island", "CR": "Costa Rica", "CW": "Curacao", "CV": "Cape Verde", "CU": "Cuba", "SZ": "Swaziland", "SY": "Syria", "SX": "Sint Maarten", "KG": "Kyrgyzstan", "KE": "Kenya", "SS": "South Sudan", "SR": "Suriname", "KI": "Kiribati", "KH": "Cambodia", "KN": "Saint Kitts and Nevis", "KM": "Comoros", "ST": "Sao Tome and Principe", "SK": "Slovakia", "KR": "South Korea", "SI": "Slovenia", "KP": "North Korea", "KW": "Kuwait", "SN": "Senegal", "SM": "San Marino", "SL": "Sierra Leone", "SC": "Seychelles", "KZ": "Kazakhstan", "KY": "Cayman Islands", "SG": "Singapore", "SE": "Sweden", "SD": "Sudan", "DO": "Dominican Republic", "DM": "Dominica", "DJ": "Djibouti", "DK": "Denmark", "VG": "British Virgin Islands", "DE": "Germany", "YE": "Yemen", "DZ": "Algeria", "US": "United States", "UY": "Uruguay", "YT": "Mayotte", "UM": "United States Minor Outlying Islands", "LB": "Lebanon", "LC": "Saint Lucia", "LA": "Laos", "TV": "Tuvalu", "TW": "Taiwan", "TT": "Trinidad and Tobago", "TR": "Turkey", "LK": "Sri Lanka", "LI": "Liechtenstein", "LV": "Latvia", "TO": "Tonga", "LT": "Lithuania", "LU": "Luxembourg", "LR": "Liberia", "LS": "Lesotho", "TH": "Thailand", "TF": "French Southern Territories", "TG": "Togo", "TD": "Chad", "TC": "Turks and Caicos Islands", "LY": "Libya", "VA": "Vatican", "VC": "Saint Vincent and the Grenadines", "AE": "United Arab Emirates", "AD": "Andorra", "AG": "Antigua and Barbuda", "AF": "Afghanistan", "AI": "Anguilla", "VI": "U.S. Virgin Islands", "IS": "Iceland", "IR": "Iran", "AM": "Armenia", "AL": "Albania", "AO": "Angola", "AQ": "Antarctica", "AS": "American Samoa", "AR": "Argentina", "AU": "Australia", "AT": "Austria", "AW": "Aruba", "IN": "India", "AX": "Aland Islands", "AZ": "Azerbaijan", "IE": "Ireland", "ID": "Indonesia", "UA": "Ukraine", "QA": "Qatar", "MZ": "Mozambique"}');
        $country_array = (array) $results->totals->threats->country;
        if (!empty($country_array)) {
            foreach ($results->totals->threats->country as $key => $value) {
                 if ($key != "XX" AND $key!="T1") {
                $res[$key]['value'] = $value;
                $res[$key]['name'] = $names->$key;
            }
            }

            if(isset($res))
            {
            array_multisort($res, SORT_NUMERIC, SORT_DESC);
            $top_threats_origin = array_slice($res, 0, 5);
            }
            else
            {

            $top_threats_origin=[];
            }
            foreach ($results->totals->requests->country as $key => $value) {
                if ($key != "XX" AND $key!="T1") {
                    $top_traffic_origins[$key]['value'] = $value;
                    $top_traffic_origins[$key]['name'] = $names->$key;
                }
            }
            arsort($top_traffic_origins, SORT_DESC);
            $top_traffic_origins = array_slice($top_traffic_origins, 0, 5);
            $search_engine = (array)$results->totals->pageviews->search_engine;
            if (!empty($search_engine)) {
                foreach ($results->totals->pageviews->search_engine as $key => $value) {
                switch ($key) {
                    case 'baiduspider':
                        $key = 'Baidu';
                        break;
                    case 'bingbot':
                        $key = 'Bing';
                        break;
                    case 'facebookexternalhit':
                        $key = 'Facebook';
                        break;
                    case 'googlebot':
                        $key = 'Google';
                        break;
                    case 'pingdom':
                        $key = 'pingdom';
                        break;
                    case 'twitterbot':
                        $key = 'Twitter';
                        break;
                    case 'yandexbot':
                        $key = 'Yandex';
                        break;
                    }
                    $top_bots[$key]['value'] = $value;
                }
                arsort($top_bots, SORT_DESC);
                $top_bots = array_slice($top_bots, 0, 5);
            } else {
                $top_bots = '';
            }
            
        } else {
            $top_threats_origin = '';
            $top_traffic_origins = '';
            $top_bots = '';
        }
        
        
        $unique_all = $results->totals->uniques->all;
        if (isset($unqiue_array)) {
            $unique_min = min($unqiue_array);
            $unique_max = max($unqiue_array);
        } else {
            $unique_min = 0;
            $unique_max = 0;
        }

        $bandwidth = $bandwidth_cached_uncached;
        $requests = $request_cached_uncached;
        $uniques = $unique_visitors;
        $threats_all = $results->totals->threats->all;
       
       // dd($results);
        $ssl = $results->totals->requests->ssl->encrypted;
        $unencrypted_ssl = $results->totals->requests->ssl->unencrypted;
        $data['total_ssl'] = $results->totals->requests->ssl->encrypted + $results->totals->requests->ssl->unencrypted;
        if ($results->totals->requests->ssl->encrypted != 0) {
            $ssl_graph = number_format(($results->totals->requests->ssl->encrypted / $data['total_ssl']) * 100, 0);
        } else
            $ssl_graph = 0;

        $top_threats[] = $results->totals->threats->country;
        $top_threat_type[] = $results->totals->threats->type;
        foreach (json_decode(json_encode($top_threats),true) as $key => $value) {
            if (!empty($value))
                $threat_country = array_keys($value, max($value));
            else 
                $threat_country = [0 => 0];
        }

        foreach (json_decode(json_encode($top_threat_type), true) as $key => $value) {
            if (!empty($value))
                $top_threats_type = array_keys($value, max($value));
            else 
                $top_threats_type = [0 => 0];
        }
        $content_type = (array)$results->totals->requests->content_type;
        if (!empty($content_type)) {
            $content_types[] = $results->totals->requests->content_type;
            foreach (json_decode(json_encode($content_types), true) as $key => $value) {
                $content_index = array_keys($value);
            }
            foreach ($content_index as $key => $value) {
                $labels[$key][] = $value;
                $labels[$key][] = $results->totals->requests->content_type->$value;
            }
            $content_type = $labels;
        } else
            $content_type = 0;
        
        $http_status = (array)$results->totals->requests->http_status;
        if (!empty($http_status)) {
            $http_statuses[] = $results->totals->requests->http_status;
            foreach (json_decode(json_encode($http_statuses), true) as $key => $value) {
                $content_index = array_keys($value);
            }
            foreach ($content_index as $key => $value) {
                $labels1[$key][] = $value;
                $labels1[$key][] = $results->totals->requests->http_status->$value;
            }
            $http_status = $labels1;
        } else
            $http_status = 0;



        $threat_type = (array)$results->totals->threats->type;
        if (!empty($threat_type)) {
            $threats_type[] = $results->totals->threats->type;
            foreach (json_decode(json_encode($threats_type), true) as $key => $value) {
                $type_index = array_keys($value);
            }
            foreach ($type_index as $key => $value) {
                $types[$key][] = $value;
                $types[$key][] = $results->totals->threats->type->$value;
            }
            $threats_mitigated = $types;
        } else
            $threats_mitigated = 0;

        if ($results->totals->requests->cached != 0)
            $servers_needed = number_format ( ($results->totals->requests->cached / $results->totals->requests->all) * 100 , 0);
        else
            $servers_needed = '';
        if ($results->totals->bandwidth->cached != 0)
            $bandwidth_saved =  $results->totals->bandwidth->all - $results->totals->bandwidth->cached;
        else
        $bandwidth_saved = '';
        $top_threats_country = $threat_country[0];
        $top_threat_type = $top_threats_type[0];
       // $data['domain_name'] = $this->session->userdata['select_domain'];
       // $timestamp = '';
        
        $data['error'] = "";




       


$new_status_array=array();
foreach ($status_codes as $key => $value) {
    
    foreach ($value as $k => $v) {
        # code...
        # 
        $arr["k_".$k]=$v;
        
    }

    $new_status_array[$key]=$arr;
    
}

$status_codes=$new_status_array;
//dd($status_codes);

$records=$zone->dns;

$zoneName= $zone->name;








///////////Logshare
///
///
///

$browserTotal=0;
$ClientRequestURI=[];
    $ClientRequestMethod=[];
    $ClientRequestUserAgent=[];
    $WAFRules=[];
    
  if($zone->els==1)
    {

    $elsAnalytics=$zone->ElsAnalytics->where('minutes',$minutes);
    // var_dump($elsAnalytics);
    if($elsAnalytics->count()>0){


   $deviceType=unserialize($elsAnalytics->where('type','deviceType')->first()->value);

   $hosts=unserialize($elsAnalytics->where('type','hosts')->first()->value);
   $referers=unserialize($elsAnalytics->where('type','referers')->first()->value);

   $clientsIP=unserialize($elsAnalytics->where('type','clientsIP')->first()->value);
if($elsAnalytics->where('type','ClientRequestURI')->first())
{
   $ClientRequestURI=unserialize($elsAnalytics->where('type','ClientRequestURI')->first()->value);
}

    if($elsAnalytics->where('type','ClientRequestMethod')->first())
    {


   $ClientRequestMethod=unserialize($elsAnalytics->where('type','ClientRequestMethod')->first()->value);
}

    if($elsAnalytics->where('type','ClientRequestUserAgent')->first()){
   $ClientRequestUserAgent=unserialize($elsAnalytics->where('type','ClientRequestUserAgent')->first()->value);
}

      if($elsAnalytics->where('type','WAFRuleID')->first())
      {
    $WAFRules=unserialize($elsAnalytics->where('type','WAFRuleID')->first()->value);

// 
   foreach ($WAFRules as $key=>$value) {
       # code...
    if ($value["key"]=="") {
        # code...
        unset($WAFRules[$key]);
    }
   }

}
   $user=parse_user_agent();



            // $wafRules=$zone->wafPackage->where('id',$pid)->first()->wafGroup->where('id',$gid)->first()->wafRule;
  
$platfomTotal=0;
   foreach ($deviceType as $key => $value) {
       # code...

    $deviceType[$key]['label']=ucfirst($deviceType[$key][0]);
    $deviceType[$key]['value']=$deviceType[$key][1];
    unset($deviceType[$key][0],$deviceType[$key][1]);
    $platfomTotal+=$deviceType[$key]['value'];
   }
   // dd($deviceType);
   $platforms=$browsers=$browserOnly=[];

$browserTotal=0;
// var_dump($ClientRequestUserAgent);
   foreach ($ClientRequestUserAgent as $key => $value) {

        $UA=parse_user_agent($value['key']);


        if(isset($browserOnly[$UA['browser']]))
        {
             $browserOnly[$UA['browser']]['value']+=$value['doc_count'];
        }
        else
        {
        
            if($UA['browser']==null)
            {
                $UA['browser']="Unknown";
            }
        $browserOnly[$UA['browser']]['label']=$UA['browser'];
        $browserOnly[$UA['browser']]['value']=$value['doc_count'];
        }
       
        $browserTotal+=$value['doc_count'];
   }

// usort($browserOnly, function ($a, $b) {
//     return $b['values'] - $a['values'];
// });
$browserOnly=array_values($browserOnly);
   // dd($browserOnly);
   foreach ($ClientRequestUserAgent as $key => $value) {
       # code...

    // dd($value);
       $UA=parse_user_agent($value['key']);

       // dd($UA);

       if($UA['platform']=="")
       {
        $UA['platform']="Unknown";
       }
       if(isset($platforms[$UA['platform']]))
       {
        $platforms[$UA['platform']]+=$value['doc_count']; 
       }
       else
       {
        $platforms[$UA['platform']]=$value['doc_count'];
       }

   // if(isset($browserOnly[$UA['browser']]))
   //     {
   //      $browserOnly[$UA['platform']]+=$value['doc_count']; 
   //     }
   //     else
   //     {
   //      $browserOnly[$UA['platform']]=$value['doc_count'];
   //     }


 // DB::insert();

       if(isset($browsers[$UA['browser']]))
       {

                if(isset($browsers[$UA['browser']][str_replace(".", "_", $UA['version'])]))
               {
                $browsers[$UA['browser']][str_replace(".", "_", $UA['version'])]+=$value['doc_count']; 
               }
               else
               {
                $browsers[$UA['browser']][str_replace(".", "_", $UA['version'])]=$value['doc_count'];
               }
                
                // $browsers[$UA['browser']]+=$value['doc_count']; 
       }
       else
       {

         if(isset($browsers[$UA['browser']][str_replace(".", "_", $UA['version'])]))
               {
                $browsers[$UA['browser']][str_replace(".", "_", $UA['version'])]+=$value['doc_count']; 
               }
               else
               {
                $browsers[$UA['browser']][str_replace(".", "_", $UA['version'])]=$value['doc_count'];
               }


        // $browsers[$UA['browser']]=$value['doc_count'];


       }
       

   }


   uasort($browsers, function ($a, $b) {
    return array_sum($b) - array_sum($a);
});

// dd($browserOnly);
   // dd($browsers);

   uasort($platforms, function ($a, $b) {
    return $b - $a;
});

   // dd($platforms);
   // dd($ClientRequestUserAgent);
    }
    else
    {
        $deviceType=$hosts =$referers = $clientsIP =$ClientRequestURI =$ClientRequestMethod =$ClientRequestUserAgent =$platforms=$browsers=$browserOnly= $platfomTotal= $WAFRules=[];
    }

}
else
{

    $deviceType=$hosts =$referers = $clientsIP =$ClientRequestURI =$ClientRequestMethod =$ClientRequestUserAgent =$platforms=$browsers= $browserOnly= $platfomTotal= $WAFRules=[];
}

                    ////Logshare end








//dd($zone);
// dd($request_uncached);
// dd($bandwidth);
// dd($bandwidth_saved);
// dd($bandwidth_cached);
// dd($bandwidth_all);
// dd($xlabel);
// dd($timestamp);


return view('admin.analytics.index', compact('zone','records','requests','request_all','request_cached','request_uncached','bandwidth','bandwidth_saved','bandwidth_cached','bandwidth_all','xlabel','timestamp','unique_all','unique_max','unique_min','uniques','threats','threats_all','top_threats_country','top_threat_type','top_threats_origin','top_traffic_origins','top_bots','servers_needed','ssl_graph','ssl','unencrypted_ssl','content_type','threats_mitigated','minutes','status_codes','zoneObj','zoneName','deviceType','hosts','referers','clientsIP','ClientRequestURI','ClientRequestMethod','ClientRequestUserAgent','browsers','browserOnly','browserTotal','platfomTotal','platforms','WAFRules'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function create()
    {
        //
    }


    public function ipDetails($zone,$minutes,$ipAddress)
    {
        //
  $zone =   Zone::where('name',$zone)->first();
            $current_time=Carbon::now('UTC');


if($zone->internalID=="")
{
    $internalID=0;
}
else{
    $internalID=$zone->internalID;
}
$body='{
  "size": 0,
  "_source": {
    "excludes": []
  },
  "aggs": {
    "2": {
      "terms": {
        "field": "RayID.keyword",
        "size": 2000,
        "order": {
          "_term": "desc"
        }
      },
      "aggs": {
        "3": {
          "top_hits": {
            "docvalue_fields": [
              "EdgeStartTimestamp"
            ],
            "_source": "EdgeStartTimestamp",
            "size": 1,
            "sort": [
              {
                "EdgeStartTimestamp": {
                  "order": "desc"
                }
              }
            ]
          }
        },
        "4": {
          "top_hits": {
            "docvalue_fields": [
              "ClientRequestHost.keyword"
            ],
            "_source": "ClientRequestHost.keyword",
            "size": 1,
            "sort": [
              {
                "EdgeStartTimestamp": {
                  "order": "desc"
                }
              }
            ]
          }
        },
        "5": {
          "top_hits": {
            "docvalue_fields": [
              "ClientRequestURI.keyword"
            ],
            "_source": "ClientRequestURI.keyword",
            "size": 1,
            "sort": [
              {
                "EdgeStartTimestamp": {
                  "order": "desc"
                }
              }
            ]
          }
        },
        "6": {
          "top_hits": {
            "docvalue_fields": [
              "ClientDeviceType.keyword"
            ],
            "_source": "ClientDeviceType.keyword",
            "size": 1,
            "sort": [
              {
                "EdgeStartTimestamp": {
                  "order": "desc"
                }
              }
            ]
          }
        },
        "7": {
          "top_hits": {
            "docvalue_fields": [
              "ClientRequestMethod.keyword"
            ],
            "_source": "ClientRequestMethod.keyword",
            "size": 1,
            "sort": [
              {
                "ClientRequestMethod.keyword": {
                  "order": "desc"
                }
              }
            ]
          }
        },
        "8": {
          "top_hits": {
            "docvalue_fields": [
              "ClientRequestProtocol.keyword"
            ],
            "_source": "ClientRequestProtocol.keyword",
            "size": 1,
            "sort": [
              {
                "EdgeStartTimestamp": {
                  "order": "asc"
                }
              }
            ]
          }
        }
      }
    }
  },
  "stored_fields": [
    "*"
  ],
  "script_fields": {},
  "docvalue_fields": [
    "@timestamp",
    "EdgeStartTimestamp"
  ],
  "query": {
    "bool": {
      "must": [
        {
          "match_all": {}
        },
        {
          "match_phrase": {
            "ZoneID": {
              "query":'.$internalID.'
            }
          }
        },
        {
          "match_phrase": {
            "ClientIP.keyword": {
               "query":"'.$ipAddress.'"
            }
          }
        },
        {
          "range": {
            "EdgeStartTimestamp": {
              "lte": '.$current_time->timestamp.',
              "gte": '.$current_time->subMinutes($minutes)->timestamp.',
              "format": "epoch_second"
            }
          }
        }
      ],
      "filter": [],
      "should": [],
      "must_not": []
    }
  }
}';
$current_time->addMinutes($minutes);
// dd($body);

$hosts = [
    'http://elasticsearch:ONiNeVB5NRDNo&F9@CgJAi7d@148.251.176.73:9201'       // HTTP Basic Authentication
   
];


// curl -XPUT http://<es node>:9200/.kibana/index-pattern/cloudflare -d '{"title" : "cloudflare",  "timeFieldName": "EdgeStartTimestamp"}'
$client = \Elasticsearch\ClientBuilder::create()
                    ->setHosts($hosts)
                    ->build();

$params = [
    'index' => 'cloudflare',
    'type' => 'doc',
    'body' => $body
];


                    $results = $client->search($params);
                    $deviceType1=$results['aggregations'][2]['buckets'];
                    $deviceType=[];
                     // dd($deviceType1);
                    foreach ($deviceType1 as $key => $value) {
                        # code...
                         $deviceType[$key]['RayID']=$deviceType1[$key]['key'];
                        $deviceType[$key]['EdgeStartTimestamp']=$deviceType1[$key]['3']['hits']['hits'][0]['fields']['EdgeStartTimestamp'][0];
                        $deviceType[$key]['ClientRequestHost']=$deviceType1[$key]['4']['hits']['hits'][0]['fields']['ClientRequestHost.keyword'][0];

                        $deviceType[$key]['ClientRequestURI']=$deviceType1[$key]['5']['hits']['hits'][0]['fields']['ClientRequestURI.keyword'][0];

                        $deviceType[$key]['ClientDeviceType']=$deviceType1[$key]['6']['hits']['hits'][0]['fields']['ClientDeviceType.keyword'][0];

                        $deviceType[$key]['ClientRequestMethod']=$deviceType1[$key]['7']['hits']['hits'][0]['fields']['ClientRequestMethod.keyword'][0];

                        $deviceType[$key]['ClientRequestProtocol']=$deviceType1[$key]['8']['hits']['hits'][0]['fields']['ClientRequestProtocol.keyword'][0];

                    }

return view('admin.analytics.ipDetails', compact('deviceType'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Analytics  $analytics
     * @return \Illuminate\Http\Response
     */
    public function show(Analytics $analytics)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Analytics  $analytics
     * @return \Illuminate\Http\Response
     */
    public function edit(Analytics $analytics)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Analytics  $analytics
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Analytics $analytics)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Analytics  $analytics
     * @return \Illuminate\Http\Response
     */
    public function destroy(Analytics $analytics)
    {
        //
    }


    public function countries($zone,$minutes) {
        

         $zone=Zone::where('name',$zone)->first();

         $names = json_decode('{"BD": "Bangladesh", "BE": "Belgium", "BF": "Burkina Faso", "BG": "Bulgaria", "BA": "Bosnia and Herzegovina", "BB": "Barbados", "WF": "Wallis and Futuna", "BL": "Saint Barthelemy", "BM": "Bermuda", "BN": "Brunei", "BO": "Bolivia", "BH": "Bahrain", "BI": "Burundi", "BJ": "Benin", "BT": "Bhutan", "JM": "Jamaica", "BV": "Bouvet Island", "BW": "Botswana", "WS": "Samoa", "BQ": "Bonaire, Saint Eustatius and Saba ", "BR": "Brazil", "BS": "Bahamas", "JE": "Jersey", "BY": "Belarus", "BZ": "Belize", "RU": "Russia", "RW": "Rwanda", "RS": "Serbia", "TL": "East Timor", "RE": "Reunion", "TM": "Turkmenistan", "TJ": "Tajikistan", "RO": "Romania", "TK": "Tokelau", "GW": "Guinea-Bissau", "GU": "Guam", "GT": "Guatemala", "GS": "South Georgia and the South Sandwich Islands", "GR": "Greece", "GQ": "Equatorial Guinea", "GP": "Guadeloupe", "JP": "Japan", "GY": "Guyana", "GG": "Guernsey", "GF": "French Guiana", "GE": "Georgia", "GD": "Grenada", "GB": "United Kingdom", "GA": "Gabon", "SV": "El Salvador", "GN": "Guinea", "GM": "Gambia", "GL": "Greenland", "GI": "Gibraltar", "GH": "Ghana", "OM": "Oman", "TN": "Tunisia", "JO": "Jordan", "HR": "Croatia", "HT": "Haiti", "HU": "Hungary", "HK": "Hong Kong", "HN": "Honduras", "HM": "Heard Island and McDonald Islands", "VE": "Venezuela", "PR": "Puerto Rico", "PS": "Palestinian Territory", "PW": "Palau", "PT": "Portugal", "SJ": "Svalbard and Jan Mayen", "PY": "Paraguay", "IQ": "Iraq", "PA": "Panama", "PF": "French Polynesia", "PG": "Papua New Guinea", "PE": "Peru", "PK": "Pakistan", "PH": "Philippines", "PN": "Pitcairn", "PL": "Poland", "PM": "Saint Pierre and Miquelon", "ZM": "Zambia", "EH": "Western Sahara", "EE": "Estonia", "EG": "Egypt", "ZA": "South Africa", "EC": "Ecuador", "IT": "Italy", "VN": "Vietnam", "SB": "Solomon Islands", "ET": "Ethiopia", "SO": "Somalia", "ZW": "Zimbabwe", "SA": "Saudi Arabia", "ES": "Spain", "ER": "Eritrea", "ME": "Montenegro", "MD": "Moldova", "MG": "Madagascar", "MF": "Saint Martin", "MA": "Morocco", "MC": "Monaco", "UZ": "Uzbekistan", "MM": "Myanmar", "ML": "Mali", "MO": "Macao", "MN": "Mongolia", "MH": "Marshall Islands", "MK": "Macedonia", "MU": "Mauritius", "MT": "Malta", "MW": "Malawi", "MV": "Maldives", "MQ": "Martinique", "MP": "Northern Mariana Islands", "MS": "Montserrat", "MR": "Mauritania", "IM": "Isle of Man", "UG": "Uganda", "TZ": "Tanzania", "MY": "Malaysia", "MX": "Mexico", "IL": "Israel", "FR": "France", "IO": "British Indian Ocean Territory", "SH": "Saint Helena", "FI": "Finland", "FJ": "Fiji", "FK": "Falkland Islands", "FM": "Micronesia", "FO": "Faroe Islands", "NI": "Nicaragua", "NL": "Netherlands", "NO": "Norway", "NA": "Namibia", "VU": "Vanuatu", "NC": "New Caledonia", "NE": "Niger", "NF": "Norfolk Island", "NG": "Nigeria", "NZ": "New Zealand", "NP": "Nepal", "NR": "Nauru", "NU": "Niue", "CK": "Cook Islands", "XK": "Kosovo", "CI": "Ivory Coast", "CH": "Switzerland", "CO": "Colombia", "CN": "China", "CM": "Cameroon", "CL": "Chile", "CC": "Cocos Islands", "CA": "Canada", "CG": "Republic of the Congo", "CF": "Central African Republic", "CD": "Democratic Republic of the Congo", "CZ": "Czech Republic", "CY": "Cyprus", "CX": "Christmas Island", "CR": "Costa Rica", "CW": "Curacao", "CV": "Cape Verde", "CU": "Cuba", "SZ": "Swaziland", "SY": "Syria", "SX": "Sint Maarten", "KG": "Kyrgyzstan", "KE": "Kenya", "SS": "South Sudan", "SR": "Suriname", "KI": "Kiribati", "KH": "Cambodia", "KN": "Saint Kitts and Nevis", "KM": "Comoros", "ST": "Sao Tome and Principe", "SK": "Slovakia", "KR": "South Korea", "SI": "Slovenia", "KP": "North Korea", "KW": "Kuwait", "SN": "Senegal", "SM": "San Marino", "SL": "Sierra Leone", "SC": "Seychelles", "KZ": "Kazakhstan", "KY": "Cayman Islands", "SG": "Singapore", "SE": "Sweden", "SD": "Sudan", "DO": "Dominican Republic", "DM": "Dominica", "DJ": "Djibouti", "DK": "Denmark", "VG": "British Virgin Islands", "DE": "Germany", "YE": "Yemen", "DZ": "Algeria", "US": "United States", "UY": "Uruguay", "YT": "Mayotte", "UM": "United States Minor Outlying Islands", "LB": "Lebanon", "LC": "Saint Lucia", "LA": "Laos", "TV": "Tuvalu", "TW": "Taiwan", "TT": "Trinidad and Tobago", "TR": "Turkey", "LK": "Sri Lanka", "LI": "Liechtenstein", "LV": "Latvia", "TO": "Tonga", "LT": "Lithuania", "LU": "Luxembourg", "LR": "Liberia", "LS": "Lesotho", "TH": "Thailand", "TF": "French Southern Territories", "TG": "Togo", "TD": "Chad", "TC": "Turks and Caicos Islands", "LY": "Libya", "VA": "Vatican", "VC": "Saint Vincent and the Grenadines", "AE": "United Arab Emirates", "AD": "Andorra", "AG": "Antigua and Barbuda", "AF": "Afghanistan", "AI": "Anguilla", "VI": "U.S. Virgin Islands", "IS": "Iceland", "IR": "Iran", "AM": "Armenia", "AL": "Albania", "AO": "Angola", "AQ": "Antarctica", "AS": "American Samoa", "AR": "Argentina", "AU": "Australia", "AT": "Austria", "AW": "Aruba", "IN": "India", "AX": "Aland Islands", "AZ": "Azerbaijan", "IE": "Ireland", "ID": "Indonesia", "UA": "Ukraine", "QA": "Qatar", "MZ": "Mozambique", "T1": "TOR"}');

         if($zone->cfaccount_id==0)
         {
            $latest=$zone->wafEvent->sortByDesc('ts')->first();
        if($latest)
        {
            $latest= $latest->ts;

        }
        else
        {
            $latest= Carbon::now()->timestamp;
        }
         // dd($minutes);

        // $latest= Carbon::now()->timestamp;
        $tsEvent = Carbon::createFromTimestamp((int)$latest)->subMinutes($minutes)->timestamp;

        //$tsEvent = Carbon::now('UTC')->subMinutes(14444)->timestamp;
    // echo($latest);

    
         $events = $zone->wafEvent->sortByDesc('ts')->where('ts','>',$tsEvent)->sortBy('ts')->groupBy('country');
       
        $threats=array();
         foreach ($events as $key=>$event) {
             # code...
             
             // dd($event);
            $threats[$key]['name']=$event->first()->country;
            $threats[$key]['code']=array_search($threats[$key]['name'], (array)$names);
            $threats[$key]['value']=$event->count();
            // $threats[$key]['SRE'] = $event->where('scope','SRE')->count();
         }
         $threats=array_values($threats);

         return response()->json($threats);
         die();
         }
        $results=unserialize($zone->analytics->where('minutes',$minutes)->first()->value);


        // $this->load->model('cloudflare_api');
        // $parameters['time_stamp'] = $this->session->userdata('time_stamp');
        // if (empty($parameters['time_stamp']))
        //     $parameters['time_stamp'] = 'last 24 hours';
        // $parameters['since'] = $this->session->userdata('since');
        // if (empty($parameters['since']))
        //     $parameters['since'] = '-1440';
        // $parameters['time_on_xaxis'] = $this->session->userdata('time_on_xaxis');
        // if (empty($parameters['time_on_xaxis']))
        //     $parameters['time_on_xaxis'] = "hour";
        // $results = $this->cloudflare_api->dashboard(array('since' => $parameters['since'] , 'until' => null, 'zone_id' => $user_id, 'email' => $this->session->userdata('user_cloudflare_api_email'), 'auth_key' => $this->session->userdata('user_cloudflare_api_key')));
        // 
        // 


        if (empty ($results)) {
            echo json_encode(false);
            exit;
        }
        

        $threats_country[] = $results->totals->threats->country;
        foreach (json_decode(json_encode($threats_country), true) as $key => $value) {
            $country_index = array_keys($value);
        }

        $country=null;
        foreach ($country_index as $key => $value) {
            $country[$key]['code'] = $value;
            $country[$key]['value'] = $results->totals->threats->country->$value;
            if ($value != "XX"  )
            $country[$key]['name'] = $names->$value;
        }



        if($country==null)
        {
            return response()->make('null');
        }
        return response()->json($country);
    }

    public function traffic($zone,$minutes) {
        

         $zone=Zone::where('name',$zone)->first();

         $country=null;

         if($zone->cfaccount_id==0)
         {
            die();
         }
        $results=unserialize($zone->analytics->where('minutes',$minutes)->first()->value);

        // $user_id = $this->session->userdata['zone_id'];
        // $this->load->model('cloudflare_api');
        // $parameters['time_stamp'] = $this->session->userdata('time_stamp');
        // if (empty($parameters['time_stamp']))
        //     $parameters['time_stamp'] = 'last 24 hours';
        // $parameters['since'] = $this->session->userdata('since');
        // if (empty($parameters['since']))
        //     $parameters['since'] = '-1440';
        // $parameters['time_on_xaxis'] = $this->session->userdata('time_on_xaxis');
        // if (empty($parameters['time_on_xaxis']))
        //     $parameters['time_on_xaxis'] = "hour";
        // $results = $this->cloudflare_api->dashboard(array('since' => $parameters['since'] , 'until' => null, 'zone_id' => $user_id, 'email' => $this->session->userdata('user_cloudflare_api_email'), 'auth_key' => $this->session->userdata('user_cloudflare_api_key')));
        // if (empty ($results)) {
        //     $data['error'] = "<a href=". site_url() . "analytics>Please refresh</a>";
        //     $data['template_file'] = 'analytics';
        //     $this->load->view('master_layout',$data);
        //     exit;
        // }
        $names = json_decode('{"BD": "Bangladesh", "BE": "Belgium", "BF": "Burkina Faso", "BG": "Bulgaria", "BA": "Bosnia and Herzegovina", "BB": "Barbados", "WF": "Wallis and Futuna", "BL": "Saint Barthelemy", "BM": "Bermuda", "BN": "Brunei", "BO": "Bolivia", "BH": "Bahrain", "BI": "Burundi", "BJ": "Benin", "BT": "Bhutan", "JM": "Jamaica", "BV": "Bouvet Island", "BW": "Botswana", "WS": "Samoa", "BQ": "Bonaire, Saint Eustatius and Saba ", "BR": "Brazil", "BS": "Bahamas", "JE": "Jersey", "BY": "Belarus", "BZ": "Belize", "RU": "Russia", "RW": "Rwanda", "RS": "Serbia", "TL": "East Timor", "RE": "Reunion", "TM": "Turkmenistan", "TJ": "Tajikistan", "RO": "Romania", "TK": "Tokelau", "GW": "Guinea-Bissau", "GU": "Guam", "GT": "Guatemala", "GS": "South Georgia and the South Sandwich Islands", "GR": "Greece", "GQ": "Equatorial Guinea", "GP": "Guadeloupe", "JP": "Japan", "GY": "Guyana", "GG": "Guernsey", "GF": "French Guiana", "GE": "Georgia", "GD": "Grenada", "GB": "United Kingdom", "GA": "Gabon", "SV": "El Salvador", "GN": "Guinea", "GM": "Gambia", "GL": "Greenland", "GI": "Gibraltar", "GH": "Ghana", "OM": "Oman", "TN": "Tunisia", "JO": "Jordan", "HR": "Croatia", "HT": "Haiti", "HU": "Hungary", "HK": "Hong Kong", "HN": "Honduras", "HM": "Heard Island and McDonald Islands", "VE": "Venezuela", "PR": "Puerto Rico", "PS": "Palestinian Territory", "PW": "Palau", "PT": "Portugal", "SJ": "Svalbard and Jan Mayen", "PY": "Paraguay", "IQ": "Iraq", "PA": "Panama", "PF": "French Polynesia", "PG": "Papua New Guinea", "PE": "Peru", "PK": "Pakistan", "PH": "Philippines", "PN": "Pitcairn", "PL": "Poland", "PM": "Saint Pierre and Miquelon", "ZM": "Zambia", "EH": "Western Sahara", "EE": "Estonia", "EG": "Egypt", "ZA": "South Africa", "EC": "Ecuador", "IT": "Italy", "VN": "Vietnam", "SB": "Solomon Islands", "ET": "Ethiopia", "SO": "Somalia", "ZW": "Zimbabwe", "SA": "Saudi Arabia", "ES": "Spain", "ER": "Eritrea", "ME": "Montenegro", "MD": "Moldova", "MG": "Madagascar", "MF": "Saint Martin", "MA": "Morocco", "MC": "Monaco", "UZ": "Uzbekistan", "MM": "Myanmar", "ML": "Mali", "MO": "Macao", "MN": "Mongolia", "MH": "Marshall Islands", "MK": "Macedonia", "MU": "Mauritius", "MT": "Malta", "MW": "Malawi", "MV": "Maldives", "MQ": "Martinique", "MP": "Northern Mariana Islands", "MS": "Montserrat", "MR": "Mauritania", "IM": "Isle of Man", "UG": "Uganda", "TZ": "Tanzania", "MY": "Malaysia", "MX": "Mexico", "IL": "Israel", "FR": "France", "IO": "British Indian Ocean Territory", "SH": "Saint Helena", "FI": "Finland", "FJ": "Fiji", "FK": "Falkland Islands", "FM": "Micronesia", "FO": "Faroe Islands", "NI": "Nicaragua", "NL": "Netherlands", "NO": "Norway", "NA": "Namibia", "VU": "Vanuatu", "NC": "New Caledonia", "NE": "Niger", "NF": "Norfolk Island", "NG": "Nigeria", "NZ": "New Zealand", "NP": "Nepal", "NR": "Nauru", "NU": "Niue", "CK": "Cook Islands", "XK": "Kosovo", "CI": "Ivory Coast", "CH": "Switzerland", "CO": "Colombia", "CN": "China", "CM": "Cameroon", "CL": "Chile", "CC": "Cocos Islands", "CA": "Canada", "CG": "Republic of the Congo", "CF": "Central African Republic", "CD": "Democratic Republic of the Congo", "CZ": "Czech Republic", "CY": "Cyprus", "CX": "Christmas Island", "CR": "Costa Rica", "CW": "Curacao", "CV": "Cape Verde", "CU": "Cuba", "SZ": "Swaziland", "SY": "Syria", "SX": "Sint Maarten", "KG": "Kyrgyzstan", "KE": "Kenya", "SS": "South Sudan", "SR": "Suriname", "KI": "Kiribati", "KH": "Cambodia", "KN": "Saint Kitts and Nevis", "KM": "Comoros", "ST": "Sao Tome and Principe", "SK": "Slovakia", "KR": "South Korea", "SI": "Slovenia", "KP": "North Korea", "KW": "Kuwait", "SN": "Senegal", "SM": "San Marino", "SL": "Sierra Leone", "SC": "Seychelles", "KZ": "Kazakhstan", "KY": "Cayman Islands", "SG": "Singapore", "SE": "Sweden", "SD": "Sudan", "DO": "Dominican Republic", "DM": "Dominica", "DJ": "Djibouti", "DK": "Denmark", "VG": "British Virgin Islands", "DE": "Germany", "YE": "Yemen", "DZ": "Algeria", "US": "United States", "UY": "Uruguay", "YT": "Mayotte", "UM": "United States Minor Outlying Islands", "LB": "Lebanon", "LC": "Saint Lucia", "LA": "Laos", "TV": "Tuvalu", "TW": "Taiwan", "TT": "Trinidad and Tobago", "TR": "Turkey", "LK": "Sri Lanka", "LI": "Liechtenstein", "LV": "Latvia", "TO": "Tonga", "LT": "Lithuania", "LU": "Luxembourg", "LR": "Liberia", "LS": "Lesotho", "TH": "Thailand", "TF": "French Southern Territories", "TG": "Togo", "TD": "Chad", "TC": "Turks and Caicos Islands", "LY": "Libya", "VA": "Vatican", "VC": "Saint Vincent and the Grenadines", "AE": "United Arab Emirates", "AD": "Andorra", "AG": "Antigua and Barbuda", "AF": "Afghanistan", "AI": "Anguilla", "VI": "U.S. Virgin Islands", "IS": "Iceland", "IR": "Iran", "AM": "Armenia", "AL": "Albania", "AO": "Angola", "AQ": "Antarctica", "AS": "American Samoa", "AR": "Argentina", "AU": "Australia", "AT": "Austria", "AW": "Aruba", "IN": "India", "AX": "Aland Islands", "AZ": "Azerbaijan", "IE": "Ireland", "ID": "Indonesia", "UA": "Ukraine", "QA": "Qatar", "MZ": "Mozambique", "T1": null}');
        $traffic_countries[] = $results->totals->requests->country;
        foreach (json_decode(json_encode($traffic_countries), true) as $key => $value) {
            $country_index = array_keys($value);
        }


        foreach ($country_index as $key => $value) {
            
            $country[$key]['code'] = $value;
            $country[$key]['value'] = $results->totals->requests->country->$value;
            if ($value != "XX"  )
                $country[$key]['name'] = $names->$value;
        
        }

        if($country==null)
        {
            return response()->make('null');
        }

        return response()->json($country);
    }

    
}
