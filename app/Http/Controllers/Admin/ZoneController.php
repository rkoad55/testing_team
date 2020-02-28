<?php

namespace App\Http\Controllers\Admin;

use App\Cfaccount;
use App\CustomCertificate;
use App\customDomain;
use App\Dns;
use App\FirewallRule;
use App\Http\Controllers\Controller;
use App\Jobs\AddCustomCertificate;
use App\Jobs\DeleteCustomCertificate;
use App\Jobs\DeletePageRule;
use App\Jobs\FetchAnalytics;
use App\Jobs\FetchCustomCertificates;
use App\Jobs\FetchDns;
use App\Jobs\FetchELSAnalytics;
use App\Jobs\FetchFirewallRules;
use App\Jobs\FetchPageRules;
use App\Jobs\FetchSpAnalytics;
use App\Jobs\FetchSpZoneSetting;
use App\Jobs\FetchUaRules;
use App\Jobs\FetchWAFPackages;
use App\Jobs\FetchZoneDetails;
use App\Jobs\FetchZoneSetting;
use App\Jobs\FetchZoneStatus;
use App\Jobs\PauseZone;
use App\Jobs\PurgeCache;
use App\Jobs\stackPath\createCustomDomain;
use App\Jobs\stackPath\DeleteCustomDomain;
use App\Jobs\stackPath\DeleteWAFRule;
use App\Jobs\stackPath\FetchCustomDomains;
use App\Jobs\stackPath\FetchWAFEvents;
use App\Jobs\stackPath\FetchWAFPolicies;
use App\Jobs\stackPath\FetchWAFRules;
use App\Jobs\stackPath\UpdateWAFRule;
use App\Jobs\stackPath\FetchELSLogs;
use App\Jobs\UpdatePageRule;
use App\Jobs\UpdateSetting;
use App\Jobs\UpdateSpSetting;
use App\Package;
use App\PageRule;
use App\PageRuleAction;
use App\panelLog;
use App\Spaccount;
use App\SpCondition;
use App\SpRule;
use App\User;
use App\wafGroup;
use App\wafPackage;
use App\Zone;
use App\ZoneSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use SSH;
use URL;
use Bouncer;
use \GuzzleHttp\Client;

use App\Libraries\Cfhost;

class ZoneController extends Controller
{

    /**
     * Show All Zones
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {

        //Only allow if user has the permissions to manage user - Admin/Resellers have this permission
        if (!Gate::allows('users_manage')) {
            return abort(401);
        }


//          Bouncer::allow('res')->to(['loadbalancer']);
// Bouncer::allow('org')->to(['seo','origin','loadbalancer']);
// Bouncer::allow('administrator')->to(['seo','origin','loadbalancer']);
 // User::find(1)->allow('loadbalancer');
        if ($request->input('type') == "sp") {
            $type = "sp"; //SP Zones
        } else {
            $type = "cf";
        }

        //Reorganized this query - will probably remove if statements
        if (auth()->user()->id == 1) {
            $users = User::whereIs('organization')->where('owner', auth()->user()->id)->with('zone')->get();
        } else {
            $users = User::whereIs('organization')->where('owner', auth()->user()->id)->with('zone')->get();

        }

        $user = User::whereIs('organization')->where('owner', auth()->user()->id)->first();

        // $spAccounts=User::find(auth()->user()->id)->getAbilities()->where('entity_type','App\Spaccount')->first()->entity_id;

        // $cfAccounts=User::find(auth()->user()->id)->getAbilities()->where('entity_type','App\Cfaccount')->first()->entity_id;

        // $zones = Zone::where('cfaccount_id',$cfAccounts)->orWhere('spaccount_id',$spAccounts)->where('owner',auth()->user()->id)->get();

        $user = User::findOrFail(auth()->user()->id);

        return view('admin.zones.index', compact('users', 'type', 'user'));
    }

    /**
     * Show Soft Deleted Zones
     * @param  Request $request Request Variable - We used this to get zone type
     * @return \Illuminate\Http\Response           Return View
     */
    
    public function trashedZones(Request $request)
    {
        //

        if (!Gate::allows('users_manage')) {
            return abort(401);
        }

        if ($request->input('type') == "sp") {
            $type = "sp"; //SP Zones
        } else {
            $type = "cf";
        }

        if (auth()->user()->id == 1) {
            $zones = Zone::onlyTrashed()->get();
        }

        // $spAccounts=User::find(auth()->user()->id)->getAbilities()->where('entity_type','App\Spaccount')->first()->entity_id;

        // $cfAccounts=User::find(auth()->user()->id)->getAbilities()->where('entity_type','App\Cfaccount')->first()->entity_id;

        // $zones = Zone::where('cfaccount_id',$cfAccounts)->orWhere('spaccount_id',$spAccounts)->where('owner',auth()->user()->id)->get();

        return view('admin.zones.trashed', compact('zones', 'type'));
    }

    /**
     * Show StackPath Zone Creation View
     * @return \Illuminate\Http\Response Return SpCreate View
     */
    public function spcreate()
    {
        //
        if (!Gate::allows('users_manage')) {
            return abort(401);
        }

        $users      = User::where('owner', auth()->user()->id)->get();
        $spaccounts = spaccount::where('reseller_id', auth()->user()->id)->get();

        return view('admin.zones.spcreate', compact('users', 'spaccounts'));
    }


    /**
     * Show Cloudflare Zone Creation View 
     * @return \Illuminate\Http\Response Return CF Create View
     */
    public function create()
    {
        //
        if (!Gate::allows('users_manage')) {
            return abort(401);
        }

        $users      = User::where('owner', auth()->user()->id)->get();
        $cfaccounts = cfaccount::where('reseller_id', auth()->user()->id)->get();
        // get packages from here
        $packages = package::all();

        return view('admin.zones.create', compact('users', 'cfaccounts', 'packages'));
    }

    
    /**
     * Store CF Domain
     * @param  Request $request 
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        if (!Gate::allows('users_manage')) {
            return abort(401);
        }

        if (auth()->user()->id == 1) // Super admin is allowed to select cfaccount from dropdown.
        {
            $cf = Cfaccount::where('id', $request->cfaccount)->first();
        } else // resellers are only allowed the specific cfaccount
        {

            $user = User::find(auth()->user()->id);
            $cf   = Cfaccount::find($user->getAbilities()->where('entity_type', 'App\Cfaccount')->first()->entity_id);


            //Check if User has not reached the zones limit
            if (!($user->branding and $user->cfZoneCount < $user->branding->cf)) {
                return abort(401);
            }
        }

        $key     = new \Cloudflare\API\Auth\APIKey($cf->email, $cf->user_api_key);
        $adapter = new \Cloudflare\API\Adapter\Guzzle($key);
       $zones   = new \Cloudflare\API\Endpoints\Zones($adapter);

        $type=$request->type;
        $created=true;
        if($type!=null)
        {

            if($cf->user_key=="")
            {


                $res=Cfhost::request("USER_LOOKUP","cloudflare_email",$cf->email);
                $user_key=$res->response->user_key;
                if($user_key!=null)
                {
                    $cf->user_key=$user_key;
                    $cf->save();
                }
            }
            else
            {
                $user_key=$cf->user_key;
            }

            // dd($user_key);

            if($user_key==null)
            {
                $created=false;
                $msg="This User is not created using the HostKey... and cannot retreive the user_key which is required for cname based zone creation";
            }
            else
            {


            // dd();
            $resolveTo=$request->resolveTo;
            // $params["user_key"]             = $arg2;
            // $params["zone_name"]            = $arg3;
            // $params["resolve_to"]           = $arg4;
            // $params["subdomains"]           = $arg5;
            $res=Cfhost::request("zone_set",$user_key,$request->name,$resolveTo.".".$request->name,"www");
             
             if(isset($res->err_code))
             {
                if($res->err_code==208)
                {
                    $created=false;
                    $msg="Zone is already configured using different partner. Please remove the zone from that account and then try adding it again";
                }
            }

            // var_dump($res->response->forward_tos->{"www.".$res->response->zone_name});
            // die();
            try {
                 $msg= "Zone ".$res->response->zone_name." Added and is set to resolve to ".$res->response->resolving_to." Please add Cname for "."<b>www.".$res->response->zone_name."</b> and point it to <b>".$res->response->forward_tos->{"www.".$res->response->zone_name}."</b> ";
                
            } catch (\ErrorException $e) {
                 echo($res->msg);
                    die;
            }
           
           

          

           $zone_id=$zones->getZoneID($request->name);

           if($created)
           {
                $zone = Zone::create([
                    "name"         => $request->name,
                    "zone_id"      => $zone_id,
                    
                    "status"       => 'active',
                    "type"         => 'partial',
                    "user_id"      => $request->user,
                    "cfaccount_id" => $cf->id,
                    "package_id"   => $request->package,

                ]);


                //Fetch Initial data in background
                FetchZoneSetting::dispatch($zone);
                FetchDns::dispatch($zone);
                FetchWAFPackages::dispatch($zone);
                FetchAnalytics::dispatch($zone);
                FetchFirewallRules::dispatch($zone);
                 $request->session()->flash('status', $msg);
                return redirect()->route('admin.zones.index');
           }
           else
           {
                 $request->session()->flash('error', $msg);
                return redirect()->route('admin.zones.create');

           }

            }
          
        }
        else
        {

            // Execute the API command for the selected CF Account
            
             
            $result = $zones->addZone($request->name, true);

            //var_dump($zr);
            if ($result) {
                $zone = Zone::create([
                    "name"         => $request->name,
                    "zone_id"      => $result->id,
                    "name_server1" => $result->name_servers[0],
                    "name_server2" => $result->name_servers[1],
                    "status"       => $result->status,
                    "type"         => $result->type,
                    "user_id"      => $request->user,
                    "cfaccount_id" => $cf->id,
                    "package_id"   => $request->package,

                ]);


                //Fetch Initial data in background
                FetchZoneSetting::dispatch($zone);
                FetchDns::dispatch($zone);
                FetchWAFPackages::dispatch($zone);
                FetchAnalytics::dispatch($zone);
                FetchFirewallRules::dispatch($zone);

                $request->session()->flash('status', 'Zone Created Successfully! Please update the DNS at domain registrar for ' . $request->name . " to <b>" . $result->name_servers[0] . "</b> & <b>" . $result->name_servers[1] . "</b>");
            }

        }

        return redirect()->route('admin.zones.index');

    }

    

    /**
     * Store stackPath Zone
     * @param  Request $request 
     * @return \Illuminate\Http\Response
     */
    public function spstore(Request $request)
    {
        //
        if (!Gate::allows('users_manage')) {
            return abort(401);
        }

        if (auth()->user()->id == 1) // Super admin is allowed to select cfaccount from dropdown.
        {
            $sp = spaccount::where('id', $request->spaccount)->first();
        } else // resellers are only allowed the specific cfaccount
        {

            $user = User::find(auth()->user()->id);
            $sp   = spaccount::find($user->getAbilities()->where('entity_type', 'App\Spaccount')->first()->entity_id);

            //Check if user has not reached the zone limit
            if (!($user->branding and $user->spZoneCount < $user->branding->sp)) {
                return abort(401);
            }

        }

        $stackPath = new \MaxCDN($sp->alias, $sp->key, $sp->secret);

        //Remove the special characted
        $name      = preg_replace("/[^a-zA-Z0-9]+/", "", $request->name);

        if (strpos($request->name, "http") !== 0) {
            $url = "http://" . $request->name;
        } else {
            $url = $request->name;
        }

        $data = [
            'name' => $name,
            'url'  => $url,
        ];
        $result = $stackPath->post('/sites', $data);

        if ($result) {
            dd("Zone Created Successfully on Remote Service, but could not be imported immedietly, please use Import functionality to add domain to system.");
        }
        dd("There was an error while creating the zone on remote server. Please hit back and check the input data. Name should be a valid hostname");
        var_dump($result);
//
        //dd($cf->user_api_key);

        //var_dump($zr);
        if ($result) {
            $zone = Zone::create([
                "name"         => $name,
                "zone_id"      => $result->id,
                "spaccount_id" => (int) $sp->id,
                "user_id"      => $request->user,
                "cfaccount_id" => 0,
            ]);

            //Fetch Initial Data
            FetchSpZoneSetting::dispatch($zone);
            FetchSpAnalytics::dispatch($zone);

            FetchWAFRules::dispatch($zone, true);
            FetchWAFPolicies::dispatch($zone);

            $request->session()->flash('status', 'Zone Created Successfully! Please update the DNS at domain registrar for ' . $request->name . " to <b>" . $result->name_servers[0] . "</b> & <b>" . $result->name_servers[1] . "</b>");
        }

        

        return redirect()->route('admin.zones.index');

    }

   
   /**
    * Show the Zone Overview Page
    * @param  String $zone Domain Name passed to this function
    * @return \Illuminate\Http\Response       Returns Zone Overview Page
    */
    public function show($zone, Request $request)
    {
// dd(auth()->user()->getAbilities()->pluck('name')->toArray());
// foreach (auth()->user()->getAbilities()->pluck('name')->toArray() as $ability)
// {
//     dd($ability);
// }


// dd(Gate::allows('analytics'));
        
        $zoneName = $zone;
        $zone     = Zone::where('name', $zone)->where('user_id', auth()->user()->id)->first();


        //If current user is the sub user then the above code returns false so we will fetch the correct zone using this code
        if (!$zone) {

            $zones = Zone::where('name', $zoneName)->get();


            //Loop through all matching zones, because there could be multiple zones in system under different resellers
            foreach ($zones as $zone) {
                
                if ($zone->user->owner == auth()->user()->id or $zone->user->id == auth()->user()->owner) {
                    break;
                }
            }
        }





        // FetchELSLogs::dispatch($zone);
        // die();
       //  $cf=$zone->cfaccount;

       //   $key     = new \Cloudflare\API\Auth\APIKey($cf->email, $cf->user_api_key);
       //  $adapter = new \Cloudflare\API\Adapter\Guzzle($key);
       // $loadBalancers   = new \Cloudflare\API\Endpoints\LoadBalancers($adapter);

       // $lb=$loadBalancers->listHealthcheckEvents($zone->zone_id);

       // dd($lb);



        // Check Zone type - We will use it below to dispatch type specific background jobs
        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

      

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {

            // dd(auth()->user()->owner);
            return abort(401);
        }
        elseif(auth()->user()->id!=$zone->user->id)
        {

            // if()
            // dd(auth()->user()->owner);
            // if(auth()->user()->owner!=1)
            // {

            // }else

            //  if(User::whereIs('reseller')->where('id',auth()->user()->owner)->count()==0)
            // {
            //     return abort(401);
            // }

            // if(User::whereIs('reseller')->where('id',auth()->user()->owner)->count()==0)
            // {
            //     return abort(401);
            // }

            $allowedZone =  $request->session()->get('zone', null);
            // dd($allowedZone);
            if($allowedZone!=null AND $allowedZone!=$zone->name)
            {
                 return abort(401);
            }

        }


        // 
        //Dispatch Background processes.
        if ($zoneType == "cfaccount") {

            FetchZoneDetails::dispatch($zone); //
            FetchPageRules::dispatch($zone, true);
            FetchAnalytics::dispatch($zone);
            FetchWAFPackages::dispatch($zone);
            FetchFirewallRules::dispatch($zone);
            FetchUaRules::dispatch($zone);
            FetchDns::dispatch($zone, true);
            FetchZoneSetting::dispatch($zone); //
            FetchCustomCertificates::dispatch($zone);
            \App\Jobs\FetchWAFEvents::dispatch($zone);

            FetchELSAnalytics::dispatch($zone);

        } else {

            FetchSpAnalytics::dispatch($zone);
            FetchSpZoneSetting::dispatch($zone);
            FetchWAFRules::dispatch($zone, true);
            FetchWAFPolicies::dispatch($zone);
            FetchCustomDomains::dispatch($zone);
            \App\Jobs\stackPath\FetchWAFEvents::dispatch($zone);





//             $hosts = [
//     'http://elasticsearch:ONiNeVB5NRDNo&F9@CgJAi7d@148.251.176.73:9201'       // HTTP Basic Authentication
   
// ];

// // dd($body);

// // curl -XPUT http://<es node>:9200/.kibana/index-pattern/cloudflare -d '{"title" : "cloudflare",  "timeFieldName": "EdgeStartTimestamp"}'
// $client = \Elasticsearch\ClientBuilder::create()
//                     ->setHosts($hosts)
//                     ->build();

//  $indexParams['index']  = 'sp_'.$zone->zone_id;   
//  $exists=$client->indices()->exists($indexParams);

// if(!$exists)
// {

// $indexName= $indexParams['index'];

// $params = [
//     'index' => $indexName,
//     'body' => [
//         'settings' => [
//             'number_of_shards' => 1,
//             'number_of_replicas' => 0
//         ],
//         'mappings' => [
//             'doc' => [
//                 '_source' => [
//                     'enabled' => false
//                 ],
//                 'properties' => [
//                     'time' => [
//                         'type' => 'date',
//                         "format" => "epoch_second"
//                     ]

//                 ]
//             ]
//         ]
//     ]
// ];




// $response = $client->indices()->create($params);


// }




// die();
// Create the index with mappings and settings now





        }


     

        if ($zoneType == "cfaccount") {

            if ($zone->status == "pending") {
                $zoneSetting = false;

                FetchZoneStatus::dispatch($zone);

            } else {
                $zoneSetting = $zone->zoneSetting;
            }
        } else {

            
            if ($zone->zoneSetting->count() == 0) {
                $zoneSetting = false;
                FetchSpZoneSetting::dispatch($zone);
            } else {
                $zoneSetting = $zone->zoneSetting;
            }

       

        }

        if ($zoneType == "cfaccount") {
            return view('admin.zones.show', compact('zone', 'zoneSetting'));
        } else {
            return view('admin.zones.spshow', compact('zone', 'zoneSetting'));
        }

    }


    /**
     * Update the Enterprise Log share Enable/Disable Status
     * @param  Request $request 
     *   
     */
    public function elsSetting(Request $request)
    {

        // dd("ytest");

        $data = $request->all();
        $zone = Zone::find($data['id']);

        if (auth()->user()->id == 1) {
            if ($data['value'] != 1) {
                $data['value'] = 0;
                $zone->els     = $data['value'];

                $zone->save();
                echo "Disabled,ELS Disabled,success";
            } else {


                if($zone->cfaccount_id!="0")
                {



                $key        = new \Cloudflare\API\Auth\APIKey($zone->cfaccount->email, $zone->cfaccount->user_api_key);
                $adapter    = new \Cloudflare\API\Adapter\Guzzle($key);
                $els        = new \Cloudflare\API\Endpoints\ELS($adapter);
                $internalID = $els->getInternalID($zone->zone_id);
                if ($internalID != "FALSE") {
                    $zone->internalID = $internalID;
                    $zone->els        = $data['value'];
                    $zone->els_bucket = $data['minutes'];
                    $zone->els_ts     = Carbon::now('UTC')->subHours($data['hours'])->timestamp;
                    $zone->save();
                    echo "Enabled, ELS Enabled,success";
                } else {

                    echo "Error!,Could not enable ELS. Please make sure that ELS is enabled at cloudflare end as well and then try again.,warning";
                }

            
            }
            else
            {

                    //


                 $hosts = [
    'http://elasticsearch:ONiNeVB5NRDNo&F9@CgJAi7d@148.251.176.73:9201'       // HTTP Basic Authentication
   
];

// dd($body);

// curl -XPUT http://<es node>:9200/.kibana/index-pattern/cloudflare -d '{"title" : "cloudflare",  "timeFieldName": "EdgeStartTimestamp"}'
$client = \Elasticsearch\ClientBuilder::create()
                    ->setHosts($hosts)
                    ->build();

 $indexParams['index']  = 'sp_'.$zone->zone_id;   
 $exists=$client->indices()->exists($indexParams);

// $params = ['index' => 'sp_'.$zone->zone_id];
// $response = $client->indices()->delete($params);

// die();
if(!$exists)
{

$indexName= $indexParams['index'];

$params = [
    'index' => $indexName,
    'body' => [
        'settings' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 0
        ],
        'mappings' => [
            'doc' => [
                '_source' => [
                    'enabled' => true
                ],
                'properties' => [
                    'time' => [
                        'type' => 'date'
                        
                    ]

                ]
            ]
        ]
    ]
];




$response = $client->indices()->create($params);




// if ($internalID != "FALSE") {
                  
                // } else {

                //     echo "Error!,Could not enable ELS. Please make sure that ELS is enabled at cloudflare end as well and then try again.,warning";
                // }

}
  $zone->internalID = $zone->zone_id;
                    $zone->els        = $data['value'];
                    $zone->els_bucket = $data['minutes'];
                    $zone->els_ts     = Carbon::now('UTC')->subHours($data['hours'])->timestamp;
                    $zone->save();
                    echo "Enabled, ELS Enabled,success";


            }

            }

            // UpdateDnsRecord::dispatch($zone,$dns->id);

        }
    }

    /**
     * Update the ZoneSetting
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  String  $zone
     * @return \Illuminate\Http\Response
     */
    public function updateSetting(Request $request, $zone)
    {
        //
        //sleep(2);
        $zone = Zone::where('name', $zone)->first();

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }

        $setId       = $request->input('id');
        $settingName = $request->input('setting');
        $setting     = $zone->zoneSetting->where('id', $setId)->where('name', $settingName)->first();

        if ($setting == null and $request->input('setting') == "zoneshield") {

            $setting = zoneSetting::create([
                'name'     => 'zoneshield',
                'zone_id'  => $zone->id,
                'editable' => 1,
                'value'    => 'Disabled',

            ]);

            $setId       = $setting->id;
            $settingName = "zoneshield";
        }

        $nameCorrections = [
            "tls_1_2_only" => " Require Modern TLS",
        ];

        if (isset($nameCorrections[$setting->name])) {
            $setName = $nameCorrections[$setting->name];
        } else {
            $setName = ucwords(str_replace("_", " ", $setting->name));
        }

        $setOld         = ucwords(str_replace("_", " ", $setting->value));
        $setting->value = $request->input('value');

        if ($setting->name == "always_use_https") {
            if ($request->input('value') == 0) {
                $setting->value = "off";
            } else {
                $setting->value = "on";
            }
        }
        $setting->save();
        $setting1 = zoneSetting::where('id', $setId)->where('name', $settingName)->first();
        $setNew   = ucwords(str_replace("_", " ", $setting1->value));

        if ($setOld == 0 and $setNew == 1) {
            $setOld = "Off";
            $setNew = "On";
        } elseif ($setOld == 1 and $setNew == 0) {
            $setOld = "On";
            $setNew = "Off";
        }

        $data = "<b>" . $setName . "</b> changed from " . $setOld . " to <b>" . $setNew . "</b>";
        echo $data;
        //echo($request->input('id'));

        if ($zone->cfaccount_id != 0) {
            UpdateSetting::dispatch($zone, $setting->id);
        } else {
            UpdateSpSetting::dispatch($zone, $setting->id);
        }

        panelLog::create([
            'user_id'    => auth()->user()->id,
            'zone_id'    => $zone->id,
            'name'       => 'Update Setting',
            'parameters' => $setting->id,
            'type'       => 3,

            'payload'    => $data,
        ]);
    }

    public function customActions(Request $request, $zone)
    {
        //

        $zone = Zone::where('name', $zone)->first();

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }
        if ($request->input('action') == "purgeCacheAll") {
            PurgeCache::dispatch($zone, true, [], []); //Zone, ALL, Files, Tags

            return response("Successfully purged all assets. Please allow up to 30 seconds for changes to take effect.");
        } elseif ($request->input('action') == "purgeFiles") {

            $files = str_replace("\r", "", $request->input('extra'));

            $files = explode("\n", $files);
            if (!is_array($files)) // User is allowed to enter comma separated URL's.
            {
                $files = explode(",", $files);
            }
            PurgeCache::dispatch($zone, false, $files, []); //Zone, ALL, Files, Tags

            return response("Successfully purged all assets. Please allow up to 30 seconds for changes to take effect.");
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Zone  $zone
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //

        if (!Gate::allows('users_manage')) {
            return abort(401);
        }

        // $zone = Zone::findOrFail($id);
        $zone = Zone::where('id', $id)->withTrashed()->first();
        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        if($zone->user)
        {


        if (!(auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {
            return abort(401);
        }
    }

        if (str_contains(URL::previous(), "trashed")) {

            if (!(auth()->user()->id == 1)) // ONly super admin can Delete Permanently.
            {
                return abort(401);

            } else {

                $zone->forceDelete(); // Force deleted if it is in Trash
                return redirect()->route('admin.zones.trash');
            }
        } else {
            $zone->delete();

            if ($zoneType == "cfaccount") {
                PauseZone::dispatch($zone, true);
            }

            return redirect()->route('admin.zones.index');
        }

    }

    public function restore(Request $request)
    {
        //

        $id = (int) $request->input('id');

        $zone = Zone::where('id', $id)->withTrashed()->first();

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        if (!(auth()->user()->id == 1)) // Only super admin can restore
        {

            return abort(401);
        }

        if (str_contains(URL::previous(), "trashed")) {

            if ($zoneType == "cfaccount") {
                PauseZone::dispatch($zone, false);
            }

            $zone->restore(); // Restore a Zone;
        } else {

        }

        return redirect()->route('admin.zones.trash');

    }

    public function crypto($zone)
    {

        $zone = Zone::where('name', $zone)->first();

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        $zoneSetting = $zone->zoneSetting;
        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }

        $customCertificates = $zone->customCertificate;

        return view('admin.zones.crypto', compact('zone', 'zoneSetting', 'customCertificates'));

    }

    public function seo($zone)
    {

        $zone = Zone::where('name', $zone)->first();

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        $zoneSetting = $zone->zoneSetting;
        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }

        return view('admin.zones.seo', compact('zone', 'zoneSetting'));

    }

    public function origin($zone)
    {

        $zone = Zone::where('name', $zone)->first();

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        $zoneSetting = $zone->zoneSetting;

        $customDomains = $zone->customDomain;
        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }

        return view('admin.zones.origin', compact('zone', 'zoneSetting', 'customDomains'));

    }

    public function createCustomDomain(Request $request)
    {
        //

        $data = $request->all();

        $zone = Zone::find($data['zid']);

        if ($zone->user->id == \Auth::user()->id or auth()->user()->id == 1) {

            // $record_id=$customDomain->resource_id;
            // $customDomain->delete();

            $domain = [
                'custom_domain' => $data['customDomain'],

                'resource_id'   => 'PENDING',
                'zone_id'       => $zone->id,
            ];

            customDomain::create($domain);

            createCustomDomain::dispatch($zone, $data['customDomain'])->onConnection("sync");

            echo "";

        }

    }
    public function deleteCustomDomain(Request $request)
    {
        //

        $data         = $request->all();
        $customDomain = customDomain::find($data['id']);

        $zone = $customDomain->zone;

        if ($zone->user->id == \Auth::user()->id or auth()->user()->id == 1) {

            $record_id = $customDomain->resource_id;
            $customDomain->delete();

            DeleteCustomDomain::dispatch($zone, $record_id);

        }

    }

    public function performance($zone)
    {

        $zone        = Zone::where('name', $zone)->first();
        $zoneSetting = $zone->zoneSetting;

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }

        if ($zoneType == "cfaccount") {
            return view('admin.zones.performance', compact('zone', 'zoneSetting'));
        } else {
            return view('admin.zones.spperformance', compact('zone', 'zoneSetting'));
        }

    }


     public function loadBalancers($zone)
    {

        $zone = Zone::where('name', $zone)->first();

        FetchPageRules::dispatch($zone, true)->onConnection('sync');

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }

        if ($zoneType == "cfaccount") {

            $pagerules = $zone->PageRule->sortByDesc("priority");

            return view('admin.pagerules.index', compact('zone', 'pagerules'));
        } else {
            die();
        }

    }

    public function pageRules($zone)
    {

        $zone = Zone::where('name', $zone)->first();

        FetchPageRules::dispatch($zone, true)->onConnection('sync');

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }

        if ($zoneType == "cfaccount") {

            $pagerules = $zone->PageRule->sortByDesc("priority");

            return view('admin.pagerules.index', compact('zone', 'pagerules'));
        } else {
            die();
        }

    }
    
    public function addPageRule(Request $request)
    {
        //

        $zone_id = $request->input('zid');

        $zone = Zone::find($zone_id);
        if (!(auth()->user()->id == $zone->user->id or auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {
            return abort(401);
        }

        $url = $request->input('url');

        $data = [
            'record_ID' => 'PENDING',
            'value'     => $url,
            'status'    => 'active',
            'priority'  => null,

            'zone_id'   => $zone_id,
        ];

        $actions = $request->input('action');
        $values  = $request->input('actionValue');
        $extra   = $request->input('extra');

        if ($actions[0] == null) {
            echo "You should add atleast one action";
            die();
        }

        $pageRule = PageRule::create($data);

        foreach ($actions as $key => $action) {

            if ($action == null) {
                echo "Please select action";
                $pageRule->delete();
                die();
            }
            # code...
            $value = $values[$key];
            if ($action == "forwarding_url") {
                $value = $values[$key] . ",SPLIT," . $extra[$key];
            }
            $data =
                [
                'pagerule_id' => $pageRule->id,
                'action'      => $action,
                'value'       => $value,

            ];

            PageRuleAction::create($data);
        }
        // echo "Rule Created";
        UpdatePageRule::dispatch($zone, $pageRule->id)->onConnection('sync');

        echo "success";
        // return redirect()->route('admin.pagerules',['zone'   =>  $zone->name]);
    }

    public function addSSL(Request $request)
    {
        

        $zone_id = $request->input('zid');

        $zone = Zone::find($zone_id);
        if (!(auth()->user()->id == $zone->user->id or auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {
            return abort(401);
        }

        

        AddCustomCertificate::dispatch($zone, $request->input('ssl'), $request->input('key'))->onConnection('sync');
       
    }

    public function addWAFRule(Request $request)
    {
        

        $zone_id = $request->input('zid');

        $zone = Zone::find($zone_id);
        if (!(auth()->user()->id == $zone->user->id or auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {
            return abort(401);
        }

        $name   = $request->input('name');
        $action = $request->input('action');

        $data = [
            'record_ID' => 'PENDING',
            'action'    => $action,
            'active'    => '1',
            'name'      => $name,

            'zone_id'   => $zone_id,
        ];

        $spRule = SpRule::create($data);

        $actions = $request->input('scope');
        $values  = $request->input('data');
        $values2 = $request->input('data2');
        $extra   = $request->input('extra');

        foreach ($actions as $key => $action) {
            # code...
            $value = $values[$key];
            if ($action == "IpRange") {
                if (ip2long($values[$key]) >= ip2long($values2[$key])) {
                    echo "IP Range invalid, Please check starting and ending IP's again.";

                    $spRule->delete();
                    die();
                }
                $value = $values[$key] . "," . $values2[$key];
            }
            $data =
                [
                'sprule_id' => $spRule->id,
                'scope'     => $action,
                'data'      => $value,

            ];

            SpCondition::create($data);
        }

        UpdateWAFRule::dispatch($zone, $spRule->id)->onConnection('sync');
       
    }

    public function editWAFRule(Request $request)
    {
      

        $zone_id = $request->input('zid');
        $rule_id = $request->input('ruleid');

        $zone = Zone::find($zone_id);
        if (!(auth()->user()->id == $zone->user->id or auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {
            return abort(401);
        }

        $name   = $request->input('name');
        $action = $request->input('action');

        $data = [

            'name'   => $name,
            'action' => $action,

        ];

        $pageRule = spRule::findOrFail($rule_id);

        $pageRule->update($data);

        $pageRule->save();

        $actions = $request->input('scope');
        $values  = $request->input('data');
        $values2 = $request->input('data2');
        $extra   = $request->input('extra');

        $actionID = $request->input('actionID');
        $delete   = $request->input('delete');
        $extra    = $request->input('extra');

        foreach ($actions as $key => $action) {
            # code...

            $value = $values[$key];
            if ($action == "IpRange") {
                $value = $values[$key] . "," . $values2[$key];
            }

            if (isset($delete[$key]) and $delete[$key] == "true") {
                $SpCondition = SpCondition::findOrFail($actionID[$key]);
                $SpCondition->delete();
            } elseif (isset($actionID[$key])) {

                $data =
                    [
                    'scope' => $action,
                    'data'  => $value,
                ];

                $SpCondition = SpCondition::findOrFail($actionID[$key]);

                $SpCondition->update($data);

                $SpCondition->save();
            } else {

                $data =
                    [
                    'sprule_id' => $pageRule->id,
                    'scope'     => $action,
                    'data'      => $value,

                ];

                SpCondition::create($data);

            }

        }
       
        UpdateWAFRule::dispatch($zone, $pageRule->id, auth()->user()->id);
        
    }

    public function editPageRule(Request $request)
    {
        //

        $zone_id = $request->input('zid');
        $rule_id = $request->input('ruleid');

        $zone = Zone::find($zone_id);
        if (!(auth()->user()->id == $zone->user->id or auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {
            return abort(401);
        }

        $url = $request->input('url');

        $data = [

            'value' => $url,

        ];

        $pageRule = PageRule::findOrFail($rule_id);

        $pageRule->update($data);

        $pageRule->save();

        $actions  = $request->input('action');
        $values   = $request->input('actionValue');
        $actionID = $request->input('actionID');
        $delete   = $request->input('delete');
        $extra    = $request->input('extra');

        if ($actions[0] == null) {
            echo "You should add atleast one action";
            die();
        }

        foreach ($actions as $key => $action) {
            # code...

            if ($action == null) {
                echo "Please select action";
                //$pageRule->delete();
                die();
            }

            $value = $values[$key];
            if ($action == "forwarding_url") {
                $value = $values[$key] . ",SPLIT," . $extra[$key];
            }

            if (isset($delete[$key]) and $delete[$key] == "true") {
                $PageRuleAction = PageRuleAction::findOrFail($actionID[$key]);
                $PageRuleAction->delete();
            } elseif (isset($actionID[$key])) {

                $data =
                    [
                    'action' => $action,
                    'value'  => $value,
                ];

                $PageRuleAction = PageRuleAction::findOrFail($actionID[$key]);

                $PageRuleAction->update($data);

                $PageRuleAction->save();
            } else {

                $data =
                    [
                    'pagerule_id' => $pageRule->id,
                    'action'      => $action,
                    'value'       => $value,

                ];

                PageRuleAction::create($data);

            }

        }

        UpdatePageRule::dispatch($zone, $pageRule->id)->onConnection('sync');

        echo "success";
        // return redirect()->route('admin.pagerules',['zone'   =>  $zone->name]);
    }

    public function sortPageRule(Request $request)
    {
        //

        $zone_id = $request->input('zid');

        $zone = Zone::find($zone_id);
        if (!(auth()->user()->id == $zone->user->id or auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {
            return abort(401);
        }

        $i = count($request->input('rule'));

        foreach ($request->input('rule') as $rule) {

            $data = [

                'priority' => $i,

            ];

            $pageRule = PageRule::findOrFail($rule);

            $pageRule->update($data);

            $pageRule->save();

            UpdatePageRule::dispatch($zone, $pageRule->id, false);

            $i--;
        }

        //echo "Rule Updated";

        // return redirect()->route('admin.pagerules',['zone'   =>  $zone->name]);
    }

    public function pageRuleStatus(Request $request)
    {

        $data = $request->all();

        $zone = PageRule::find($data['id'])->zone;

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {
            return abort(401);
        }
        if ($data['value'] != 'active') {
            $data['value'] = 'disabled';
        }

        $pageRule         = pageRule::where('id', $data['id'])->first();
        $pageRule->status = $data['value'];

        $pageRule->save();

        UpdatePageRule::dispatch($zone, $pageRule->id);

    }

    public function destroyPageRule(Request $request)
    {
        //

        $data     = $request->all();
        $PageRule = PageRule::find($data['id']);

        $zone = $PageRule->zone;

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {
            return abort(401);
        }

        $rule_id = $PageRule->record_id;

        foreach ($PageRule->pageruleaction as $pageruleaction) {
            $pageruleaction->delete();
        }

        $PageRule->delete();

        DeletePageRule::dispatch($zone, $rule_id);

    }

    public function destroyCustomCertificate(Request $request)
    {
        //

        $data              = $request->all();
        $CustomCertificate = CustomCertificate::find($data['id']);

        $zone = $CustomCertificate->zone;

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {
            return abort(401);
        }

        $rule_id = $CustomCertificate->resource_id;

        $CustomCertificate->delete();

        DeleteCustomCertificate::dispatch($zone, $rule_id);

    }

    public function destroyWAFRule(Request $request)
    {
        //

        $data   = $request->all();
        $sprule = spRule::find($data['id']);

        $zone = $sprule->zone;

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->id == $zone->user->owner or auth()->user()->id == 1)) {
            return abort(401);
        }

        $rule_id = $sprule->record_id;

        foreach ($sprule->SpCondition as $pageruleaction) {
            $pageruleaction->delete();
        }

        $sprule->delete();

        DeleteWAFRule::dispatch($zone, $rule_id);

    }

    public function caching($zone)
    {

        $zone        = Zone::where('name', $zone)->first();
        $zoneSetting = $zone->zoneSetting;

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }

        if ($zoneType == "cfaccount") {
            return view('admin.zones.caching', compact('zone', 'zoneSetting'));
        } else {
            return view('admin.zones.spcaching', compact('zone', 'zoneSetting'));
        }

    }

    public function changeOwnership($zoneId)
    {

        $zone = Zone::findOrFail((int) $zoneId);

        // dd($zone);

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        if (!(auth()->user()->id == 1)) {
            return abort(401);
        }

        // dd($zone->name);

        $users = User::whereIs('organization')->where('owner', auth()->user()->id)->with('roles')->get();

        return view('admin.zones.ownership', compact('zone', 'users'));

    }

    public function storeOwnership($zoneId, Request $request)
    {

        $zone = Zone::findOrFail((int) $zoneId);

        // dd($zone);

        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        if (!(auth()->user()->id == 1)) {
            return abort(401);
        }

        // dd($zone->name);

        $zone->user_id = $request->input('user');

        $zone->save();

        $request->session()->flash('status', ' Ownership of ' . $zone->name . " Changed to <b>" . $zone->user->name . "</b> (<b>" . $zone->user->email . "</b> )");

        return redirect()->route('admin.zones.index');

    }

    public function network($zone)
    {
        

        $zone = Zone::where('name', $zone)->first();
        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }

        $zoneSetting = $zone->zoneSetting;

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }

        return view('admin.zones.network', compact('zone', 'zoneSetting'));

    }

    public function contentProtection($zone)
    {

        $zone = Zone::where('name', $zone)->first();
        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }
        $zoneSetting = $zone->zoneSetting;

        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }

        return view('admin.zones.contentProtection', compact('zone', 'zoneSetting'));

    }


    public function contentZone(Request $request,$zone)
    {

      /*  if ($request->input('type') == "sp") {
           echo  $type = "sp"; //SP Zones
        } else {
          echo   $type = "cf";
        }

        */

/*
        if (auth()->user()->id == 1) {
               $users = User::whereIs('organization')->where('owner', auth()->user()->id)->with('zone')->get();
        } else {
            echo  $users = User::whereIs('organization')->where('owner', auth()->user()->id)->with('zone')->get();

        }
  */     
/*
        
         $user = User::whereIs('organization')->where('owner', auth()->user()->id)->first();

        // $spAccounts=User::find(auth()->user()->id)->getAbilities()->where('entity_type','App\Spaccount')->first()->entity_id;

        // $cfAccounts=User::find(auth()->user()->id)->getAbilities()->where('entity_type','App\Cfaccount')->first()->entity_id;

        // $zones = Zone::where('cfaccount_id',$cfAccounts)->orWhere('spaccount_id',$spAccounts)->where('owner',auth()->user()->id)->get();

         $user = User::findOrFail(auth()->user()->id);

      if (auth()->user()->id == 1) {
          $zones = Zone::onlyTrashed()->get();
    }


     $users      = User::where('owner', auth()->user()->id)->get();
        echo  $spaccounts = spaccount::where('reseller_id', auth()->user()->id)->get();

        


       
        
        $character = json_decode($spaccounts);
        echo $character->id;

        */

/*

        if (auth()->user()->id == 1) // Super admin is allowed to select cfaccount from dropdown.
        {
             $cf = Cfaccount::where('id', $request->cfaccount)->first();

        
        } else // resellers are only allowed the specific cfaccount
        {

             $user = User::find(auth()->user()->id);
               $cf   = Cfaccount::find($user->getAbilities()->where('entity_type', 'App\Cfaccount')->first()->entity_id);
echo "ok";

            //Check if User has not reached the zones limit
            if (!($user->branding and $user->cfZoneCount < $user->branding->cf)) {
                return abort(401);
            }
        }
        
       
      //  return $cf->user->email;
    echo  $key     = new \Cloudflare\API\Auth\APIKey($cf->email, $cf->user_api_key);
        // $adapter = new \Cloudflare\API\Adapter\Guzzle($key);
       // $zones   = new \Cloudflare\API\Endpoints\Zones($adapter);

         //echo $character["key"];

      //echo $character['2']->key;
//return $request[];


/*
        $zone = Zone::where('name', $zone)->first();
        if ($zone->cfaccount_id != 0) {
            $zoneType = "cfaccount";
        } else {
            $zoneType = "spaccount";
        }
         $zoneSetting = $zone->zoneSetting;
     echo  $zone->user->id ;
        if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
            return abort(401);
        }

      //  return view('admin.zones.contentZone', compact('zone', 'zoneSetting'));

        

*/

  $zone = Zone::where('name', $zone)->first();
if ($zone->cfaccount_id != 0) {
    $zoneType = "cfaccount";
} else {
    $zoneType = "spaccount";
}
$zoneSetting = $zone->zoneSetting;

if (!(auth()->user()->id == $zone->user->id or auth()->user()->owner == $zone->user->id or auth()->user()->id == $zone->{$zoneType}->reseller->id or auth()->user()->id == 1)) {
    return abort(401);
}
 $useride=$zone->user_id;
echo "<br>";
 $cfaccount_id=$zone->cfaccount_id;
echo "<br/>";
 $zone_id=$zone->zone_id;
echo "<br/>";
//echo $users      = User::where('id',auth()->user()->id )->get();
  $cfaccounts = cfaccount::where('id', $cfaccount_id)->first();
echo "<br/>";
  $email=$cfaccounts->email;
 echo "<br/>";
  $user_api_key=$cfaccounts->user_api_key;
 echo "<br/>";

/*



 $id = 'security_level';

 //Your account email address
 $email = $email;
 
 //Account API Key. you can get it from account settings
 $key = $user_api_key;
 
 //Domain identifier. you can get it by clicking on domain name then go to DNS page for example and right click on page
 //and click get page source and search for {"zones" then you will find [{"id":"XXXXX". XXXXX is the zone identifier
 $zone_identifier = $zone_id;
 
 $req = array(
   'http'=>array(
     'ignore_errors' => true,
     'method'=>"GET",
     'header'=>
     "X-Auth-Email: $email\r\n" .
     "X-Auth-Key: $key\r\n" .
     "Content-Type: application/json\r\n"
   )
 );
 
 $context = stream_context_create($req);
 
 $response = file_get_contents('https://api.cloudflare.com/client/v4/zones/023e105f4ecef8ad9ca31a8372d0c353/available_rate_plans', false, $context);
 
 //print response
 echo $response;

 */
/*

 $token = "kdqJmAgbOj5gfZXTTgzBMxcjm6FWxEcCE98cIVgD";
 $key = "kdqJmAgbOj5gfZXTTgzBMxcjm6FWxEcCE98cIVgD";
 //setup the request, you can also use CURLOPT_URL
 $ch = curl_init('https://api.cloudflare.com/client/v4/zones/023e105f4ecef8ad9ca31a8372d0c353/available_rate_plans');
 
 // Returns the data/output as a string instead of raw data
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
 //Set your auth headers
 curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    "X-Auth-Email: $email" ,
     "X-Auth-Key: $key" 
    ));
 
 // get stringified data/output. See CURLOPT_RETURNTRANSFER
 echo $data = curl_exec($ch);
 echo "<br/>";
 echo "<br/>";
 // get info about the request
  $info = curl_getinfo($ch);
  print_r ($info);
 // close curl resource to free up system resources
 curl_close($ch);
 
 */

/*
$postRequest = array(
    'Content-Type'=> 'application/json',
    "X-Auth-Email"=> $email ,
     "X-Auth-Key"=> $user_api_key 
);

$cURLConnection = curl_init('https://api.cloudflare.com/client/v4/zones/023e105f4ecef8ad9ca31a8372d0c353/available_rate_plans');
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postRequest);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

echo $apiResponse = curl_exec($cURLConnection);
curl_close($cURLConnection);

// $apiResponse - available data from the API request
 $jsonArrayResponse = json_decode($apiResponse);
*/

 $ch = curl_init();
$headers = array(
                 'X-Auth-Email:'.$email,
                 'X-Auth-Key:' .$user_api_key,
                 'Content-Type: application/json',
                  );
$data = array(
              'value' => 'off',
               );

               
$json = json_encode($data);
curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/$zone_id/settings/rocket_loader");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_exec($ch);
curl_close($ch);
echo "<br/>";

$ch = curl_init();
$headers = array(
                 'X-Auth-Email:'.$email,
                 'X-Auth-Key:' .$user_api_key,
                 'Content-Type: application/json',
                  );
//$data = array(
  //  addEventListener['fetch', event =>  [event.respondWith(fetch(event.request))] ]
    //           );

               
$json = json_encode($data);
curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/$zone_id/workers/routes");
//curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_exec($ch);
curl_close($ch);



}

}
