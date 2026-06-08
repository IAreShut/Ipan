<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use Mailtrap\Bridge\Transport\MailtrapSdkTransportFactory;

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
        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Register Mailtrap SDK transport factory
        $this->app->extend('mail.manager', function ($manager) {
            $manager->extend('mailtrap+sdk', function () {
                return (new MailtrapSdkTransportFactory())->create(
                    new \Symfony\Component\Mailer\Transport\Dsn(
                        'mailtrap+sdk',
                        config('mail.mailers.mailtrap.host', 'sandbox.api.mailtrap.io'),
                        config('mail.mailers.mailtrap.api_token'),
                        null,
                        null,
                        ['inboxId' => config('mail.mailers.mailtrap.inbox_id')]
                    )
                );
            });
            return $manager;
        });

        Queue::after(function (JobProcessed $event) {
            Mail::purge();
        });

        Queue::failing(function (JobFailed $event) {
            Mail::purge();
        });
    }
}

