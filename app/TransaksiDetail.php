<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'transaksi_detail';

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
    protected $fillable = ['id','store_id', 'transaksi_id', 'product_id', 'harga', 'qty', 'weight', 'note'];

    
}
