<?php

namespace Zerp\Jitsi\Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();
        Artisan::call('cache:clear');

        $permission = [                       
            // JitsiMeeting settings
            ['name' => 'manage-jitsi-settings', 'module' => 'jitsi settings', 'label' => 'Manage Jitsi Meet Settings'],
            ['name' => 'edit-jitsi-settings', 'module' => 'jitsi settings', 'label' => 'Edit Jitsi Meet Settings'],

            // JitsiMeeting management
            ['name' => 'manage-jitsi-meetings', 'module' => 'jitsi-meetings', 'label' => 'Manage Jitsi Meetings'],
            ['name' => 'manage-any-jitsi-meetings', 'module' => 'jitsi-meetings', 'label' => 'Manage All Jitsi Meetings'],
            ['name' => 'manage-own-jitsi-meetings', 'module' => 'jitsi-meetings', 'label' => 'Manage Own Jitsi Meetings'],
            ['name' => 'view-jitsi-meetings', 'module' => 'jitsi-meetings', 'label' => 'View Jitsi Meetings'],
            ['name' => 'create-jitsi-meetings', 'module' => 'jitsi-meetings', 'label' => 'Create Jitsi Meetings'],
            ['name' => 'edit-jitsi-meetings', 'module' => 'jitsi-meetings', 'label' => 'Edit Jitsi Meetings'],
            ['name' => 'delete-jitsi-meetings', 'module' => 'jitsi-meetings', 'label' => 'Delete Jitsi Meetings'],
            ['name' => 'join-jitsi-meetings', 'module' => 'jitsi-meetings', 'label' => 'Join Jitsi Meetings'],
            ['name' => 'start-jitsi-meetings', 'module' => 'jitsi-meetings', 'label' => 'Start Jitsi Meetings'],
            ['name' => 'update-jitsi-status', 'module' => 'jitsi-meetings', 'label' => 'Update Jitsi Meet Status'],
        ];

        $company_role = Role::where('name', 'company')->first();

        foreach ($permission as $perm) {
            $permission_obj = Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                [
                    'module' => $perm['module'],
                    'label' => $perm['label'],
                    'add_on' => 'Jitsi',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            if ($company_role && !$company_role->hasPermissionTo($permission_obj)) {
                $company_role->givePermissionTo($permission_obj);
            }
        }
    }
}