<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the student and leader roles if they don't exist
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'leader', 'guard_name' => 'web']);

        // Assign student role to all users who don't have any roles (except admins)
        $users = User::query()->get();

        foreach ($users as $user) {
            if ($user->hasRole('admin')) {
                continue; // Skip admin users
            }

            $user->assignRole($studentRole);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove student role from users who only have that role
        $studentRole = Role::where('name', 'student')->first();
        if ($studentRole) {
            User::query()
                ->whereHas('roles', function ($query) use ($studentRole) {
                    $query->where('role_id', $studentRole->id);
                })
                ->each(function ($user) use ($studentRole) {
                    $user->removeRole($studentRole);
                });
        }

        // Delete the roles
        Role::where('name', 'student')->delete();
        Role::where('name', 'leader')->delete();
    }
};
