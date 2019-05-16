<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'saldos';

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
    protected $fillable = ['user_id', 'admin_id', 'saldo', 'jumlah_transfer', 'no_rek', 'status', 'created_at', 'updated_at'];
}
