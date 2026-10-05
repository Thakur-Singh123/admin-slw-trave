<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    //Call migration
    protected $table = 'usc_admin';

    protected $primaryKey = 'adm_id';

    public $timestamps = false;

    protected $fillable = [
        'adm_login_id',
        'adm_password',
        'adm_conpwd',
        'adm_name',
        'adm_status',
        'adm_privi',
        'adm_email',
    ];

    protected $hidden = [
        'adm_password',
        'adm_conpwd',
    ];

    //Function for get auth name
    public function getAuthPasswordName() {
        return 'adm_password';
    }
    
    //Function for get auth password
    public function getAuthPassword() {
        return $this->adm_password;
    }
}