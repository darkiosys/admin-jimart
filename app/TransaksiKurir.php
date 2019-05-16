<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransaksiKurir extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'transaksi_kurir';

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
    protected $fillable = ['transaksi_id','store_id', 'kurir', 'waktu', 'biaya'];
    
}
