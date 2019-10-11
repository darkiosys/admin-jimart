<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class T_bonusgenerasi extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 't_bonusgeneration';

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
        'reff_id',
        'username',
        'level',
        'amount'
    ];

    
}
