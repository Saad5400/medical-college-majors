<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'data-entry',
            'guard_name' => 'web',
        ]);

        $permissionNames = [
            'view-any Facility',
            'view Facility',
            'create Facility',
            'update Facility',
            'delete Facility',
            'delete-any Facility',
            'view-any FacilitySeat',
            'view FacilitySeat',
            'create FacilitySeat',
            'update FacilitySeat',
            'delete FacilitySeat',
            'delete-any FacilitySeat',
            'view-any Specialization',
            'view Specialization',
            'create Specialization',
            'update Specialization',
            'delete Specialization',
            'delete-any Specialization',
            'view-any Track',
            'view Track',
            'create Track',
            'update Track',
            'delete Track',
            'delete-any Track',
            'view-any TrackSpecialization',
            'view TrackSpecialization',
            'create TrackSpecialization',
            'update TrackSpecialization',
            'delete TrackSpecialization',
            'delete-any TrackSpecialization',
        ];

        $permissions = collect($permissionNames)
            ->map(fn (string $name): Permission => Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]))
            ->all();

        $role->syncPermissions($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::where('name', 'data-entry')->delete();
    }
};
