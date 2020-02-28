<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Zone;
use App\SpAnalytics;
use Carbon\Carbon;
use App\wafEvent;


class FetchWAFEvents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user_id,$zone;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Zone $zone)
    {
        //
        $this->zone=$zone;
        $this->user_id=auth()->user()->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        

       



 $key     = new \Cloudflare\API\Auth\APIKey($this->zone->cfaccount->email, $this->zone->cfaccount->user_api_key);
        $adapter = new \Cloudflare\API\Adapter\Guzzle($key);
        $waf   = new \Cloudflare\API\Endpoints\WAF($adapter);



        $events=$waf->getEvents($this->zone->zone_id,1,20);
        


$i=0;

$events=(array)$events;
//dd($events);
//ini_set('max_execution_time', '0');
        foreach ($events['result'] as $event) {
            # code...
           

        // dd($event->ray_id);

         // print_r($event['0']->ray_id);
          // die();
          
            $check['resource_id']=$event->ray_id;
            $check['zone_id']=$this->zone->id;



            
         
       // die();
        $evente['client_ip']=$event->ip;

        $evente['rule_id']=$event->rule_id;

        $evente['country']=$event->country;
        $evente['method']=$event->method;

        $evente['type']=$event->kind;

        $evente['uri']=$event->uri;

        $evente['action']=$event->action;

        $evente['ref_id']=$event->rule_id;

        $evente['user_agent']=$event->ua;
        $evente['scheme']=$event->scheme;

        $evente['request_type']=$event->source;
        $evente['rule_name']=$event->kind;

        
            $evente['scheme']=$event->proto;
            $evente['domain']=$event->host;
           // $event['rule_name']=$event['rule_message'];
            $evente['timestamp']=strtotime($event->occurred_at);
            $evente['count']=1;
           // $evente['ref_id']='';

            // if($event['rule_id']==null)
            // {   
            //     $event['rule_id']=0;
            //     var_dump($event['rule_name']);
            //      // $event['rule_info']=$event['rule_info']->value;
            // }

           
             $check['resource_id']."<br>";
             
            unset($event->ray_id,$event->ip,$event->proto,$event->host,$evente['request_duration'],$evente['triggered_rule_ids'],$evente['cloudflare_location'],$evente['occurred_at'],$evente['rule_detail'],$evente['type'],$evente['rule_info']);
            //var_dump($event);

            
           
            $insertedEvent=wafEvent::updateOrCreate($check,$evente);
            // dd($insertedEvent);
        // $i++;

        }



       
        
    }

  
}
