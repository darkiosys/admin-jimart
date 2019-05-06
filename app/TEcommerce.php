<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TdEcommerce extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 't_ecommerce';

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
    protected $fillable = ['store_id', 'kurir', 'waktu', 'biaya'];   
}
