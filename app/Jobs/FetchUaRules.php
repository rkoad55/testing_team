<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Zone;
use App\UaRule;


class FetchUaRules implements ShouldQueue
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
        $UARules   = new \Cloudflare\API\Endpoints\UARules($adapter);



       $rules=$UARules->listRules($this->zone->zone_id,1,100);


     

      
       /* Cloudflare.com | APİv4 | Api Ayarları 
        $apikey = $this->zone->cfaccount->user_api_key; // Cloudflare Global API
        $email = $this->zone->cfaccount->email; // Cloudflare Email Adress
       $domain = 'domainhere';  // zone_name // Cloudflare Domain Name
        $zoneid = $this->zone->zone_id; // zone_id // Cloudflare Domain Zone ID
   
 
       // A-record oluşturur DNS sistemi için.
               $ch = curl_init("https://api.cloudflare.com/client/v4/zones/".$zoneid."/firewall/ua_rules");
               curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
               curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
               curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
               curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                     
               curl_setopt($ch, CURLOPT_HTTPHEADER, array(
               'X-Auth-Email: '.$email.'',
               'X-Auth-Key: '.$apikey.'',
               'Cache-Control: no-cache',
               // 'Content-Type: multipart/form-data; charset=utf-8',
               'Content-Type:application/json',
               'purge_everything: true'
               
               ));
           
               // -d curl parametresi.
               $data = array(

                'page' => 1,
                'per_page' => 20


               /*
                   'type' => 'A',
                   'name' => ''.$dnsadgeldi.'',
                   'content' => ''.$dnsipgeldi.'',
                   'zone_name' => ''.$domain.'',
                   'zone_id' => ''.$zoneid.'',
                   'proxiable' => 'true',
                   'proxied' => true,
                   'ttl' => '120'

                   
                 
               );
               
               $data_string = json_encode($data);
   
               curl_setopt($ch, CURLOPT_POST, true);
               curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);	
               //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data_string));
   
               $sonuc = curl_exec($ch);
   
                    // If you want show output remove code slash.
             print_r($sonuc);
   
               curl_close($ch);

echo "<br/>";
echo "<br/>";

             //  $token = "kdqJmAgbOj5gfZXTTgzBMxcjm6FWxEcCE98cIVgD";
               //setup the request, you can also use CURLOPT_URL
               $ch = curl_init("https://api.cloudflare.com/client/v4/zones/".$zoneid."/security/events");
               
               // Returns the data/output as a string instead of raw data
               curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
               
               //Set your auth headers
               curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-Auth-Email: '.$email.'',
                'X-Auth-Key: '.$apikey.'',
                'Cache-Control: no-cache',
                // 'Content-Type: multipart/form-data; charset=utf-8',
                'Content-Type:application/json',
                'purge_everything: true',
                'page: 1',
                'per_page: 20'
                  ));
                  curl_setopt($ch, CURLOPT_HTTPGET, true);
               // get stringified data/output. See CURLOPT_RETURNTRANSFER
               echo $data = curl_exec($ch);
               echo "<br/>";
               echo "<br/>";
               // get info about the request
                $info = curl_getinfo($ch);
                print_r ($info);
               // close curl resource to free up system resources
               curl_close($ch);

       //  dd($rules);

         die();
         */
        //
       // $rules=json_decode(json_encode($rules),true);
       // dd($rules);
            foreach ($rules->result as $rule) {
    $rule=json_decode(json_encode($rule),true);
   // dd($rule);
    $check['zone_id'] = $this->zone->id;
    $check['record_id']    = $rule['id'];

    $rule['value']=$rule['configuration']['value'];
    $rule['target']=$rule['configuration']['target'];
    $rule['mode']=$rule['mode'];
    $rule['paused']=$rule['paused'];
    $rule['description']=$rule['description'];

    
    array_forget($rule,["id","configuration"]);

    // dd($rule);
    //dd($rule);
            
      

           
         UaRule::updateOrCreate($check, $rule);
          
            

}
    }
}
