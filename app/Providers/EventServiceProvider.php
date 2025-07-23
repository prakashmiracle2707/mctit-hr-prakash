<?php

namespace App\Providers;

use App\Events\VerifyReCaptchaToken;
use App\Listeners\VerifyReCaptchaTokenLis;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use App\Listeners\Saml2LoginEventListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        VerifyReCaptchaToken::class => [
            VerifyReCaptchaTokenLis::class,
        ],
        Saml2LoginEvent::class => [
            Saml2LoginEventListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
