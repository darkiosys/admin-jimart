<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
	/**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'members';
	
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'sponsor',
        'username',
        'password',
        'email',
        'phone',
        'tgl_lahir',
        'jenis_kelamin',
        'photo',
        'saldo',
        'points',
        'subdistrict_id',
        'ip_address',
        'last_login',
        'status',
        'token',
        'upline',
        'posisi',
        'bank',
        'norek',
        'an',
        'adminrp',
        'tgl',
        'tglaktif',
        'paket',
        'blokir',
        'membership',
        'fo',
        'stocklist',
        'reward1',
        'reward2',
        'reward3',
        'reward4',
        'reward5',
        'reward6',
        'jabatan',
        'store_name',
        'store_address',
        'store_note',
        'store_kode_pos',
        'store_status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password',
    // ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
