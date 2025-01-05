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

        $admin = Role::query()->create(['name' => 'admin']);

        $saad = User::query()->create([
            'name' => 'Saad Batwa',
            'email' => 'sdbtwa@gmail.com',
            'password' => bcrypt('1'),
        ]);

        $saad->assignRole($admin);

        for ($i = 0; $i < 2; $i++) {
            Major::query()->create([
                'name' => 'مسار رقم ' . ($i + 1),
                'max_users' => 18,
            ]);
        }

        for ($i = 0; $i < 2; $i++) {
            User::query()->create([
                'name' => 'طالب رقم ' . ($i + 1),
                'email' => 'student' . ($i + 1) . '@example.com',
                'password' => bcrypt('1'),
                'student_id' => '123456' . ($i + 1),
                'gpa' => rand(0, 100) / 10,
            ]);
        }
    }
}
