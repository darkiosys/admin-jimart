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
    protected $table = 'td_ecommerce';

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
    protected $fillable = ['t_ecommerce_id', 'products_id', 'members_id', 'price', 'weight', 'total_qty', 'total_weight', 'subtotal', 'note', 'status_bonus', 'transaksi_id'];   
}
