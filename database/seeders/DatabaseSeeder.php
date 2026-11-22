<?php

namespace Database\Seeders;

use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
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
        Role::firstOrCreate(['name' => 'data-entry']);

        $admin = User::query()->firstOrCreate(
            ['email' => 'sdbtwa@gmail.com'],
            [
                'name' => 'Saad Batwa',
                // Never a fixed password: this repository is public and the
                // seeder has run against production (`migrate --seed`). Set
                // SEED_ADMIN_PASSWORD, or reset the password after seeding.
                'password' => bcrypt(env('SEED_ADMIN_PASSWORD') ?: Str::password(32)),
            ]
        );

        $admin->assignRole('admin');

        if (! Track::query()->exists()) {
            for ($i = 0; $i < 15; $i++) {
                Track::query()->create([
                    'name' => 'Track '.($i + 1),
                    'max_users' => 20,
                ]);
            }
        }

        if (app()->environment(['local', 'testing'])) {
            $this->call(TestDataSeeder::class);
        }
    }
}
