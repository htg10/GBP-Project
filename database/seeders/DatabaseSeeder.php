<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\User;
use App\Models\Client;
use App\Models\GbpLocation;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $agency = Agency::firstOrCreate(
            ['slug' => 'demo-agency'],
            ['name' => 'Demo Agency']
        );

        User::updateOrCreate(
            ['agency_id' => $agency->id, 'email' => 'admin@demo.com'],
            ['name' => 'Super Admin', 'role' => 'SUPER_ADMIN', 'password' => Hash::make('admin123')]
        );
        User::updateOrCreate(
            ['agency_id' => $agency->id, 'email' => 'owner@demo.com'],
            ['name' => 'Demo Owner', 'role' => 'AGENCY_OWNER', 'password' => Hash::make('password123')]
        );
        User::updateOrCreate(
            ['agency_id' => $agency->id, 'email' => 'staff@demo.com'],
            ['name' => 'Staff User', 'role' => 'STAFF', 'password' => Hash::make('staff123')]
        );

        Subscription::updateOrCreate(
            ['agency_id' => $agency->id],
            ['plan' => 'GROWTH', 'status' => 'ACTIVE']
        );

        $client = Client::firstOrCreate(
            ['agency_id' => $agency->id, 'name' => 'Bright Smile Dental'],
            ['industry' => 'Healthcare', 'email' => 'hello@brightsmile.in']
        );

        GbpLocation::firstOrCreate(
            ['client_id' => $client->id, 'google_name' => 'accounts/123/locations/456'],
            ['title' => 'Bright Smile Dental — Ghaziabad', 'address' => 'Ghaziabad, Uttar Pradesh']
        );

        $this->command->info('Seeded!');
        $this->command->info('  ADMIN: admin@demo.com / admin123');
        $this->command->info('  OWNER: owner@demo.com / password123');
        $this->command->info('  STAFF: staff@demo.com / staff123');
    }
}
