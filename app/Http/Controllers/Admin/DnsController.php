<?php

namespace App\Http\Controllers\Admin;

use App\Dns;
use App\Zone;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Jobs\UpdateDnsRecord;
use App\Jobs\DeleteDNS;
use App\Jobs\FetchDns;
class DnsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function index($zone=Null)
    
    {   
//return "ok";

   $zone=Zone::where('name',$zone)->first();

     FetchDns::dispatch($zone,true)->onConnection('sync');
     
           $records=$zone->Dns->sortBy("type");
         
        if(!(auth()->user()->id == $zone->user->id OR \App\User::find(auth()->user()->id)->owner == $zone->user->id OR auth()->user()->id == 1))
    {
            return abort(401);
    }
    

        //FetchDns::dispatch($zone)->onConnection("sync");
        // dd($records->count());
        // foreach ($records as $record) {
        //     # code...
        //     dump($record->name);
        //     dump($record->content);
        //     dump($record->type);
        //     dump($record->type);
        // }

        
        
        return view('admin.dns.index', compact('records','zone'));
        
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createDNS(Request $request)
    {
        //

        $zone_id=$request->input('zid');

        $zone= Zone::find($zone_id);
        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->cfaccount->reseller->id OR auth()->user()->id == 1))
    {
            return abort(401);
    }

        $type=$request->input('type');
        $name=$request->input('name');
        $content=$request->input('content');

        $ttl=$request->input('ttl');
        // echo($ttl);
        // die();

        // $existingRecord=$zone->Dns->where("name",$name)->first();
        // if($existingRecord)
        // {
        //     echo "Record for this hostname already exists";
        // }
        $data=[
            'record_ID'  =>  'PENDING',
            'type'  =>  $type,
            'name'  =>  $name,
            'content'   =>  $content,
            'locked'    => 0,
            'ttl' => $ttl,
            'zone_id'   => $zone_id,
        ];

        if($type=="A")
        {


            if($this->reserved_ip($content))
            {
                $data['proxiable']=0;
                $data['proxied']=0;
            }
            else
            {
                $data['proxiable']=1;
                $data['proxied']=1;
            }
            
        }
        else
        {
            $data['proxiable']=0;
            $data['proxied']=0;
        }
        $record=DNS::create($data);


        UpdateDnsRecord::dispatch($zone,$record->id)->onConnection('sync');

        echo "success";
         //return redirect()->route('admin.dns',['zone'   =>  $zone->name]);
    }



   public function reserved_ip($ip)
{
    $reserved_ips = array( // not an exhaustive list
    '167772160'  => 184549375,  /*    10.0.0.0 -  10.255.255.255 */
    '3232235520' => 3232301055, /* 192.168.0.0 - 192.168.255.255 */
    '2130706432' => 2147483647, /*   127.0.0.0 - 127.255.255.255 */
    '2851995648' => 2852061183, /* 169.254.0.0 - 169.254.255.255 */
    '2886729728' => 2887778303, /*  172.16.0.0 -  172.31.255.255 */
    '3758096384' => 4026531839, /*   224.0.0.0 - 239.255.255.255 */
    );

    $ip_long = sprintf('%u', ip2long($ip));

    foreach ($reserved_ips as $ip_start => $ip_end)
    {
        if (($ip_long >= $ip_start) && ($ip_long <= $ip_end))
        {
            return TRUE;
        }
    }
    return FALSE;
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
    public function update(Request $request, Dns $dns)
    {
        //

        $data=$request->all();
        $dns=Dns::find($data['pk']);

        if(isset($dns->zone))
        {
                $zone=$dns->zone;
        }
        else
        {
            echo "error";
            die();
        }
        

        if(!(auth()->user()->id == $zone->user->id OR auth()->user()->id == $zone->cfaccount->reseller->id OR auth()->user()->id == 1))
    {
            return abort(401);
    }

        


            if($data['name']=='name')
            {   

                $data['value']=rtrim($data['value'],".");
                if(ends_with($data['value'],$zone->name))
                {

                }
                elseif($data['value']!=$zone->name)
                {
                    $data['value'].=".".$zone->name;
                }
            }


            $dns=Dns::where('id', $data['pk'])->first();
            $dns->{$data['name']}=$data['value'];

            $dns->save();


            UpdateDnsRecord::dispatch($zone,$dns->id)->onConnection('sync');
            // if(DB::table('dns')
            // ->where('id', $data['pk'])
            // ->update([$data['name'] => $data['value']]))
            // {
            //      return response(json_encode(true), 200)
            //       ->header('Content-Type', 'text/plain');
            // }
        
        

        //echo($request->input('pk'));
    }

    public function dnsProxy(Request $request)
    {

        $data=$request->all();
        // dd($data);
        $dns=Dns::where('id',$data['id'])->first();
        if(isset($dns->zone))
        {
                $zone=$dns->zone;
        }
        else
        {
            echo "error";
            die();
        }
        $zone=$dns->zone;

        if($zone->user->id == \Auth::user()->id OR auth()->user()->id==1)
        {   
        if($data['value']!="true")
        {
            $data['value']=0;
        }
        else
        {
            $data['value']=1;
        }

        $dns=Dns::where('id', $data['id'])->first();
        $dns->proxied=$data['value'];


        $dns->save();
        // dd($dns);
        UpdateDnsRecord::dispatch($zone,$dns->id);

    }
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
        $dns=Dns::find($data['id']);

         if(isset($dns->zone))
        {
                $zone=$dns->zone;
        }
        else
        {
            echo "error";
            die();
        }
        
        

        if($zone->user->id == \Auth::user()->id  OR auth()->user()->id==1)
        {   

            $record_id=$dns->record_id;
            $dns->delete();

            DeleteDNS::dispatch($zone,$record_id);




        }


    }
}
