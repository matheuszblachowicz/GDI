<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class Group extends Model
{
    /**
     * The object classes of the LDAP model.
     */
    public static array $objectClasses = [];
}
