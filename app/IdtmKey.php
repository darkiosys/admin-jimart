<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IdtmKey extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'idtmkey';

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
    protected $fillable = ['idtm_pin', 'idtm_key', 'created'];
}
