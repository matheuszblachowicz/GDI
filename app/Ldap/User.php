<?php

namespace App\Ldap;

use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class User extends LdapUser
{
    protected $guarded = [];

    protected $baseDn = 'OU=Ti,OU=JDI,OU=FASTEFOOD,DC=fastefood,DC=local';
}