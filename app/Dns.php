<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dns extends Model
{
    protected $table = 'dns';

    protected $guarded = ['id'];

   


     public function zone()
    {
        return $this->belongsTo('App\Zone');
    }

}
