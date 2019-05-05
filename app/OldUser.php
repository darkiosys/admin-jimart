<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OldUser extends Model
{
	/**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'old_member';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'pass', 'saldo'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'pass',
    ];
}
