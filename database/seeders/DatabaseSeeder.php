<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $name = config('app.admin.name');
        $email = config('app.admin.email');
        $password = config('app.admin.password');

        if ($name && $email && $password) {
            $admin = User::firstOrNew(['email' => $email]);
            $admin->name = $name;
            $admin->password = $password;
            $admin->forceFill(['is_admin' => true])->save();
        }
    }
}
