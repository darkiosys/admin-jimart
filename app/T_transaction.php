<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class T_transaction extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 't_transaction';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id',
        'log_id',
        'target',
        'reff_id',
        'prodname',
        'amount',
        'status',
        'message',
        'time',
        'response',
        'payload'
    ];

    
}
