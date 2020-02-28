<?php
// error_reporting(-1);

namespace App\Http\Controllers\Admin;

use App\ZoneSetting;
use App\Zone;
use App\Cfaccount;
use App\FirewallRule;
use App\wafGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Jobs\UpdateWAFGroup;
use App\Jobs\UpdateWAFPackage;
use App\Jobs\UpdateFirewallRule;
use App\Jobs\UpdateUaRule;
use App\Jobs\UpdateWAFRule;
use App\UaRule;

use App\wafRule;
use App\Jobs\FetchFirewallRules;
use App\Jobs\FetchUaRules;
use App\Jobs\UpdateSPWAF;
use App\Jobs\DeleteFirewallRule;
use App\Jobs\FetchWAFEvents;


use App\Jobs\DeleteUaRule;
class FirewallController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index($zone=Null)
    {   
		
		// ini_set('memory_limit', '-1');

        $zone=Zone::where('name',$zone)->first();

        //$cf = Cfaccount::where('id',$zone->cfaccount_id)->first();

       $cfaccount = new FetchFirewallRules($zone);
 
         // return $cf;
       // return "ok";

     $cfaccount->handle();
     

     $cfaccount2 = new FetchWAFEvents($zone);
 
         // return $cf;
       // return "ok";

	 $cfaccount2->handle();
	
      
        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->user->owner OR auth()->user()->id == 1))
    {
            return abort(401);
    }

        
        $records=$zone->ZoneSetting;
        $zoneSetting=$zone->ZoneSetting;


        // foreach ($records as $record) {
        //     # code...
        //     dump($record->name);
        //     dump($record->content);
        //     dump($record->type);
        //     dump($record->type);
            
        // } 


         $wafPackages=$zone->wafPackage;
         $events=$zone->wafEvent->sortBy('timestamp')->take(50000);

       // echo  count($events);
       //  die();
        //die();

    // $ok=$zone->wafEvent->sortBy('timestamp')->take(500);
        // echo "$ok";
        // die();        

        if($zone->cfaccount_id!=0)
        {   


            FetchFirewallRules::dispatch($zone)->onConnection('sync');
            FetchUaRules::dispatch($zone)->onConnection('sync');
            $rules=$zone->FirewallRule;

            $uaRules=$zone->UaRule;
            return view('admin.firewall.index', compact('records','zone','zoneSetting','rules','uaRules','wafPackages','events'));    
        }
        else
        {
            $rules=$zone->SpRule;
            // dd($rules);
            
            // dd($events);
            return view('admin.spfirewall.index', compact('records','zone','zoneSetting','rules','wafPackages','events'));
        }
        
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function createAccessRule(Request $request)
    {
        //

        $zone_id=$request->input('zid');

        $zone= Zone::find($zone_id);
        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->cfaccount->reseller->id OR auth()->user()->id == 1))
    {
            return abort(401);
    }

        $target=$request->input('target');
    
        $value=$request->input('value');
         $mode=$request->input('mode');
$notes=$request->input('note');

       
        $data=[
            'record_ID'  =>  'PENDING',
            'target'  =>  $target,
            'value'  =>  $value,
            'mode'   =>  $mode,
            'scope'    => 'zone',
            'status' => 'active',
		'notes' => $notes,
            'zone_id'   => $zone_id,
        ];

       
        $record=FirewallRule::create($data);


        UpdateFirewallRule::dispatch($zone,$record->id)->onConnection('sync');

        echo "success";
         //return redirect()->route('admin.dns',['zone'   =>  $zone->name]);
    }

    public function createUaRule(Request $request)
    {
        //

        $zone_id=$request->input('zid');

        $zone= Zone::find($zone_id);
        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->cfaccount->reseller->id OR auth()->user()->id == 1))
    {
            return abort(401);
    }

        $description=$request->input('description');
    
        $value=$request->input('value');
         $mode=$request->input('mode');

       
        $data=[
            'record_ID'  =>  'PENDING',
            'description'  =>  $description,
            'value'  =>  $value,
            'mode'   =>  $mode,
            
            'paused' => false,
            'zone_id'   => $zone_id,
        ];

       
        $record=UaRule::create($data);


        UpdateUaRule::dispatch($zone,$record->id)->onConnection('sync');

        echo "success";
         //return redirect()->route('admin.dns',['zone'   =>  $zone->name]);
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
     * @param  \App\Dns  $dns
     * @return \Illuminate\Http\Response
     */
    public function show(Dns $dns)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Dns  $dns
     * @return \Illuminate\Http\Response
     */
    public function edit(Dns $dns)
    {
        //


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Dns  $dns
     * @return \Illuminate\Http\Response
     */


        public function wafGroupDetails($zone,$pid,$gid)
    {
        //
  $zone =   Zone::where('name',$zone)->first();
            

            $wafRules=$zone->wafPackage->where('id',$pid)->first()->wafGroup->where('id',$gid)->first()->wafRule;
            // dd($wafRules->wafRule);
            // 
            
                    return view('admin.firewall.wafGroupDetails', compact('wafRules'));
    }

        public function editUaRule(Request $request)
    {
        //



        $zone_id=$request->input('zid');
        $rule_id= $request->input('ruleid');

        $zone= Zone::find($zone_id);
        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->user->owner OR auth()->user()->id == 1))
    {
            return abort(401);
    }

        $value=$request->input('value');
        $description=$request->input('description');
        $mode=$request->input('mode');
        
        $data=[
            
            'value'  =>  $value,
            'description'  =>  $description,
            'mode'  =>  $mode
    
        ];

         $uaRule = UaRule::findOrFail($rule_id);
          
           $uaRule->update($data);

           $uaRule->save();


       
        

        
        
         UpdateUaRule::dispatch($zone,$uaRule->id)->onConnection('sync');

         echo "success";
         // return redirect()->route('admin.pagerules',['zone'   =>  $zone->name]);
    }
    public function updateFirewallRule(Request $request, $zone)
    {
        //

        $zone=Zone::where('name',$zone)->first();

        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->user->owner OR auth()->user()->id == 1))
    {
            return abort(401);
    }

         $rule=$zone->FirewallRule->where('id',$request->input('id'))->first();

        //echo($rule->mode);
        $rule->mode=$request->input('value');
        $rule->save();
        // $rule1=Zone::where('name',$zone)->first()->FirewallRule->where('id',$request->input('id'))->first();
        //   echo($rule1->mode);
        //   
        
        UpdateFirewallRule::dispatch($zone, $rule->id);

        echo "Access Rule Updated";
   
    }

        public function updateUaRule(Request $request, $zone)
    {
        //

        $zone=Zone::where('name',$zone)->first();

        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->user->owner OR auth()->user()->id == 1))
    {
            return abort(401);
    }

         $rule=$zone->UaRule->where('id',$request->input('id'))->first();

        //echo($rule->mode);
        $rule->mode=$request->input('value');
        $rule->save();
        // $rule1=Zone::where('name',$zone)->first()->FirewallRule->where('id',$request->input('id'))->first();
        //   echo($rule1->mode);
        //   
        
        UpdateUaRule::dispatch($zone, $rule->id);

        echo "Access Rule Updated";
   
    }

        public function uaRuleStatus(Request $request)
    {

        $data=$request->all();

        $zone=UaRule::find($data['id'])->zone;

       if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->user->owner OR auth()->user()->id == 1))
    {
            return abort(401);
    }
        if($data['value']!='1')
        {
            $data['value']='0';
        }

        $UaRule=UaRule::where('id', $data['id'])->first();
        $UaRule->paused=$data['value'];

        $UaRule->save();

        UpdateUaRule::dispatch($zone,$UaRule->id)->onConnection('sync');

    
    }
    public function updateWafGroup(Request $request, $zone)
    {
        //

        $zone = Zone::where('name',$zone)->first();
        // dd($zone);
        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->user->owner OR auth()->user()->id == 1))
    {
            return abort(401);
    }

         // $rule=Zone::where('name',$zone)->first()->wafPackage->whereIn('id',function ($query) {
         //        $query->select('package_id')->from('waf_groups')
         //        ->Where('id','=','valueRequired');
        $id=$request->input('id');

        $wafGroup = wafGroup::where('id',$id)->first();


        if($request->input('value')=="true")
        {
            $value="on";
        }
        else
        {
            $value="off";
        }

       
        $wafGroup->mode=$value;
        $wafGroup->save();
       



        
        if($zone->cfaccount_id!=0)
        {
            UpdateWAFGroup::dispatch($zone, $wafGroup->id);
        }
        else
        {
            UpdateSPWAF::dispatch($zone, $wafGroup->id);
        }
        
        
    }
    public function updateWafRule(Request $request, $zone)
    {
        //

        $zone = Zone::where('name',$zone)->first();
        // dd($zone);
        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->user->owner OR auth()->user()->id == 1))
    {
            return abort(401);
    }

         // $rule=Zone::where('name',$zone)->first()->wafPackage->whereIn('id',function ($query) {
         //        $query->select('package_id')->from('waf_groups')
         //        ->Where('id','=','valueRequired');
        $id=$request->input('id');

        $wafRule = wafRule::where('id',$id)->first();

        $value=$request->input('value');
        

       
        $wafRule->mode=$value;
        $wafRule->save();
       



        
        if($zone->cfaccount_id!=0)
        {
            UpdateWAFRule::dispatch($zone, $wafRule->id)->onConnection('sync');
        }
        else
        {
            // UpdateSPWAF::dispatch($zone, $wafGroup->id);
        }
        
        echo "success";
        
    }

public function updateWafPackage(Request $request, $zone)
    {
        //


         // $rule=Zone::where('name',$zone)->first()->wafPackage->whereIn('id',function ($query) {
         //        $query->select('package_id')->from('waf_groups')
         //        ->Where('id','=','valueRequired');
            $id=$request->input('id');

         $zone = Zone::where('name',$zone)->first();

         if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->user->owner OR auth()->user()->id == 1))
    {
            return abort(401);
    }


        $wafPackage = $zone->wafPackage->where('id',$id)->first();

        echo $wafPackage->{$request->input('setting')};
        $wafPackage->{$request->input('setting')}=$request->input('value');

        $wafPackage->save();

        $wafPackage = $zone->wafPackage->where('id',$id)->first();

        echo $wafPackage->{$request->input('setting')};

        // if($request->input('value')=="true")
        // {
        //     $value="on";
        // }
        // else
        // {
        //     $value="off";
        // }

       
       
       



       

        UpdateWAFPackage::dispatch($zone, $wafPackage->id);
        
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Dns  $dns
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //

        $data=$request->all();
        $firewallRule=FirewallRule::find($data['id']);

        
        $zone=$firewallRule->zone;

        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->user->owner OR auth()->user()->id == 1))
    {
            return abort(401);
    }

        

            $rule_id=$firewallRule->record_id;
            $firewallRule->delete();

            DeleteFirewallRule::dispatch($zone,$rule_id)->onConnection('sync');




        


    }

    public function destroyUaRule(Request $request)
    {
        //

        $data=$request->all();
        $UaRule=UaRule::find($data['id']);

        
        $zone=$UaRule->zone;

        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->user->owner OR auth()->user()->id == 1))
    {
            return abort(401);
    }

        

            $rule_id=$UaRule->record_id;
            $UaRule->delete();

            DeleteUaRule::dispatch($zone,$rule_id);




        


    }
}
