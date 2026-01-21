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

        $admin = User::query()->firstOrCreate(
            ['email' => 'sdbtwa@gmail.com'],
            [
                'name' => 'Saad Batwa',
                'password' => bcrypt('1'),
            ]
        );

        $admin->assignRole('admin');

        if (! Track::query()->exists()) {
            for ($i = 0; $i < 15; $i++) {
                Track::query()->create([
                    'name' => 'مسار رقم '.($i + 1),
                    'max_users' => 20,
                ]);
            }
        }

        if (app()->environment(['local', 'testing'])) {
            $this->call(TestDataSeeder::class);
        }
    }
}
