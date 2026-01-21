<?php

namespace Database\Seeders;

use App\Models\Track;
use App\Models\User;
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

        // Create roles if they don't exist
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'leader']);

        if (User::query()->where('email', 'sdbtwa@gmail.com')->exists()) {
            return;
        }

        $saad = User::query()->create([
            'name' => 'Saad Batwa',
            'email' => 'sdbtwa@gmail.com',
            'password' => bcrypt('1'),
        ]);

        $saad->assignRole('admin');

        for ($i = 0; $i < 15; $i++) {
            Track::query()->create([
                'name' => 'مسار رقم '.($i + 1),
                'max_users' => 18,
            ]);
        }
    }
}
