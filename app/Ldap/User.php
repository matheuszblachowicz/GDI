<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use LdapRecord\Laravel\Auth\HasLdapUser;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;

class User extends Authenticatable implements LdapAuthenticatable
{
    use HasLdapUser, AuthenticatesWithLdap;

    // Campos que serão preenchidos na importação
    protected $fillable = [
        'name', 'email', 'password', 'guid', 'domain',
    ];


    public static function boot()
{
    parent::boot();

    static::addGlobalScope('ou_ti_fastfood', function ($query) {
        // Ajustado para o domínio fastfood.local
        $query->in('OU=Ti,OU=JDI,OU=FASTEFOOD,DC=fastefood,DC=local'); 
    });
}
}