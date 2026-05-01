<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::whereIn('key', [
            'site_name',
            'landing_html',
            'github_url',
            'footer_copyright',
            'website_url'
        ])
            ->get()
            ->pluck('value', 'key');

        return view('landing', [
            'siteName' => $settings->get('site_name', 'Universal Credits'),
            'landingHtml' => $settings->get('landing_html', '<h1>Welcome to Universal Credits</h1>'),
            'githubUrl' => $settings->get('github_url', 'https://github.com'),
            'footerCopyright' => $settings->get('footer_copyright', 'All rights reserved.'),
            'websiteUrl' => $settings->get('website_url', 'https://odehit.com'),
        ]);
    }
}
