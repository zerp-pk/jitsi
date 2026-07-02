<?php

namespace Zerp\Jitsi\Helpers;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class JitsiUtility
{
     public static function GivePermissionToRoles($role_id = null, $rolename = null)
    {
        $permission = [
            'manage-jitsi-meetings',
            'manage-own-jitsi-meetings',  
            'view-jitsi-meetings',
            'join-jitsi-meetings',
            'start-jitsi-meetings'
        ];
            
            if ($rolename == 'staff') {
            $roles_v = Role::where('name', 'staff')->where('id', $role_id)->first();
                foreach ($permission as $permission_v) {
                    $permission = Permission::where('name', $permission_v)->first();
                    if (!empty($permission)) {
                        if (!$roles_v->hasPermissionTo($permission_v)) {
                            $roles_v->givePermissionTo($permission);
                        }
                    }
                }
            }

            if ($rolename == 'client') {
                $roles_v = Role::where('name', 'client')->where('id', $role_id)->first();
                foreach ($permission as $permission_v) {
                    $permission = Permission::where('name', $permission_v)->first();
                    if (!empty($permission)) {
                        if (!$roles_v->hasPermissionTo($permission_v)) {
                            $roles_v->givePermissionTo($permission);
                        }
                    }
                }
            }        
    }
}