<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ServiceConfigProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadServiceSettings();
    }

    protected function loadServiceSettings()
    {
        try {
            // Google OAuth config
            $googleClientId = config_get('login_social.google.client_id') ?: config('services.google.client_id');
            $googleClientSecret = config_get('login_social.google.client_secret') ?: config('services.google.client_secret');
            $googleRedirect = config_get('login_social.google.redirect') ?: config('services.google.redirect');

            if (empty($googleRedirect) || (app()->environment('production') && str_contains($googleRedirect, 'localhost'))) {
                $googleRedirect = url('/auth/google/callback');
            }

            if (!empty($googleClientId)) {
                config([
                    'services.google.client_id' => $googleClientId,
                    'services.google.client_secret' => $googleClientSecret,
                    'services.google.redirect' => $googleRedirect,
                ]);
            }

            // Facebook OAuth config
            $fbClientId = config_get('login_social.facebook.client_id') ?: config('services.facebook.client_id');
            $fbClientSecret = config_get('login_social.facebook.client_secret') ?: config('services.facebook.client_secret');
            $fbRedirect = config_get('login_social.facebook.redirect') ?: config('services.facebook.redirect');

            if (empty($fbRedirect) || (app()->environment('production') && str_contains($fbRedirect, 'localhost'))) {
                $fbRedirect = url('/auth/facebook/callback');
            }

            if (!empty($fbClientId)) {
                config([
                    'services.facebook.client_id' => $fbClientId,
                    'services.facebook.client_secret' => $fbClientSecret,
                    'services.facebook.redirect' => $fbRedirect,
                ]);
            }
        } catch (\Throwable $e) {
            // Silently ignore if DB is not ready
        }
    }
}