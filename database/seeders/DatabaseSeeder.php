<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('permissions:sync', ['-P' => true]);

        if (Role::query()->where('name', 'admin')->exists()) {
            return;
        }

        $admin = Role::query()->create(['name' => 'admin']);

        $saad = User::query()->create([
            'name' => 'Saad Batwa',
            'email' => 'sdbtwa@gmail.com',
            'password' => bcrypt('1'),
        ]);

        $saad->assignRole($admin);

        for ($i = 0; $i < 15; $i++) {
            Major::query()->create([
                'name' => 'مسار رقم '.($i + 1),
                'max_users' => 18,
            ]);
        }
    }
}
