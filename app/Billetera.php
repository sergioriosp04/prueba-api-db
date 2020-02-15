<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Billetera extends Model
{
    protected $table = 'billetera';

    protected $fillable = [
        'user_id', 'saldo',
    ];

    public function user(){
        return $this->belongsTo('App\User');
    }
}
