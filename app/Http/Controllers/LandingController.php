<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::whereIn('key', ['site_name', 'landing_html'])
            ->get()
            ->pluck('value', 'key');

        return view('landing', [
            'siteName' => $settings->get('site_name', 'Universal Credits'),
            'landingHtml' => $settings->get('landing_html', '<h1>Welcome to Universal Credits</h1>'),
        ]);
    }
}
