<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'site_name' => 'Universal Credits',
            'github_url' => 'https://github.com/qahmad81/universal-credits',
            'footer_copyright' => 'All rights reserved to OdehIT.com',
            'website_url' => 'https://odehit.com',
            'contact_email' => '',
            'landing_html' => '<h1>Universal Credits</h1><p>The open-source unified payment protocol for the agent economy.</p>',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
