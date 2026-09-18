<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\User;
use App\Models\Plan;
use App\Models\CreditPackage;
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

        // ---- Plans (Super Admin managed, GST-inclusive) ----
        $this->seedPlans();

        // ---- Credit packages (clients buy to top up) ----
        foreach ([
            ['name' => 'Starter Pack', 'credits' => 250, 'price' => 499, 'sort' => 1],
            ['name' => 'Booster Pack', 'credits' => 1000, 'price' => 1799, 'sort' => 2],
            ['name' => 'Power Pack', 'credits' => 2000, 'price' => 2999, 'sort' => 3],
            ['name' => 'Agency Pack', 'credits' => 5000, 'price' => 6999, 'sort' => 4],
        ] as $p) {
            CreditPackage::updateOrCreate(['name' => $p['name']], array_merge($p, ['gst_rate' => 18, 'is_active' => true]));
        }

        $this->command->info('Seeded!');
        $this->command->info('  SUPER ADMIN: admin@demo.com / admin123');
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
