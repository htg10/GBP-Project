<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\User;
use App\Models\Client;
use App\Models\GbpLocation;
use App\Models\Plan;
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
        Subscription::updateOrCreate(
            ['agency_id' => $agency->id],
            ['plan' => 'GROWTH', 'status' => 'ACTIVE']
        );

        $client = Client::firstOrCreate(
            ['agency_id' => $agency->id, 'name' => 'Bright Smile Dental'],
            ['industry' => 'Healthcare', 'email' => 'hello@brightsmile.in']
        );

        // Client Owner (sees only their own client's live GBP data).
        User::updateOrCreate(
            ['agency_id' => $agency->id, 'email' => 'client@demo.com'],
            ['name' => 'Bright Smile Owner', 'role' => 'CLIENT_OWNER', 'client_id' => $client->id, 'password' => Hash::make('clientpass123')]
        );
        // Staff bound to that client.
        User::updateOrCreate(
            ['agency_id' => $agency->id, 'email' => 'staff@demo.com'],
            ['name' => 'Staff User', 'role' => 'STAFF', 'client_id' => $client->id, 'password' => Hash::make('staff123')]
        );

        GbpLocation::firstOrCreate(
            ['client_id' => $client->id, 'google_name' => 'accounts/123/locations/456'],
            ['title' => 'Bright Smile Dental — Ghaziabad', 'address' => 'Ghaziabad, Uttar Pradesh']
        );

        // ---- Plans (Super Admin managed, GST-inclusive) ----
        $this->seedPlans();

        $this->command->info('Seeded!');
        $this->command->info('  SUPER ADMIN: admin@demo.com / admin123');
        $this->command->info('  CLIENT OWNER: client@demo.com / clientpass123');
        $this->command->info('  STAFF: staff@demo.com / staff123');
    }

    private function seedPlans(): void
    {
        $all = array_keys(Plan::MODULES);
        $plans = [
            ['name' => 'Starter', 'code' => 'STARTER', 'price' => 999, 'credits' => 100, 'sort' => 1,
                'features' => ['1 GBP location', 'Reviews + AI reply', 'Photo posting'],
                'permissions' => ['reviews', 'gbp_content', 'ai_media', 'audit']],
            ['name' => 'Growth', 'code' => 'GROWTH', 'price' => 2999, 'credits' => 500, 'sort' => 2,
                'features' => ['Multiple GBP locations', 'Social posting', 'Lead CRM + WhatsApp'],
                'permissions' => ['reviews', 'gbp_content', 'ai_media', 'audit', 'competitors', 'rank_checker', 'social', 'whatsapp', 'leads', 'keywords', 'ai_mode']],
            ['name' => 'Agency', 'code' => 'AGENCY', 'price' => 9999, 'credits' => 2000, 'sort' => 3,
                'features' => ['Unlimited clients', 'Ads reporting', 'White label', 'Invoicing & Tally'],
                'permissions' => $all],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(['code' => $p['code']], array_merge($p, ['gst_rate' => 18, 'is_active' => true]));
        }
    }
}
