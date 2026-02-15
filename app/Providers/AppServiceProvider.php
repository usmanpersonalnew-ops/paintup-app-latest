<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        
        // Force HTTPS for asset URLs when behind a proxy (ngrok)
        if (request()->header('X-Forwarded-Proto') === 'https' || 
            request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        
        // Explicitly bind 'zone' parameter to ProjectRoom model
        // This prevents Laravel from auto-binding to QuoteZone model
        \Illuminate\Support\Facades\Route::model('zone', \App\Models\ProjectRoom::class);

        // Share settings globally with all views
        try {
            $settings = Setting::getSettings();
            View::share('appSettings', [
                'company_name' => $settings->company_name,
                'logo_path' => $settings->logo_path,
                'primary_color' => $settings->primary_color,
                'secondary_color' => $settings->secondary_color,
                'support_whatsapp' => $settings->support_whatsapp,
                'support_email' => $settings->support_email,
                'footer_text' => $settings->footer_text,
                'gst_number' => $settings->gst_number,
                'address' => $settings->address,
                'invoice_prefix' => $settings->invoice_prefix,
            ]);
        } catch (\Exception $e) {
            // If database not ready, use defaults
            View::share('appSettings', [
                'company_name' => 'PaintUp',
                'logo_path' => null,
                'primary_color' => '#2563eb',
                'secondary_color' => '#1e293b',
                'support_whatsapp' => null,
                'support_email' => null,
                'footer_text' => null,
                'gst_number' => null,
                'address' => null,
                'invoice_prefix' => 'INV',
            ]);
        }
    }
}
