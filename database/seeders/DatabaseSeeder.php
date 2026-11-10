<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();

        $this->call([
            SettingSeeder::class,
            CatalogSeeder::class,
            ContentSeeder::class,
            // Real projects and clients; runs on every seed, adds only what is missing.
            ProjectSeeder::class,
        ]);
    }

    private function seedAdmin(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@creativeitbari.com');

        if (User::where('email', $email)->exists()) {
            $this->command?->info("Admin user {$email} already exists — password unchanged.");

            return;
        }

        $password = 'ADMIN_PASSWORD';

        User::create([
            'name' => 'Administrator',
            'email' => $email,
            'password' => $password,
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $this->command?->warn("Admin login → {$email} / {$password}  (change it after first login)");
    }
}
