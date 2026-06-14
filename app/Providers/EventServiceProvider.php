<?php

namespace App\Providers;

use App\Events\InvoiceCreated;
use App\Events\PaymentCompleted;
use App\Listeners\LogInvoiceActivity;
use App\Listeners\SendInvoiceWhatsApp;
use App\Listeners\SendPaymentWhatsApp;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        InvoiceCreated::class => [
            LogInvoiceActivity::class,
            SendInvoiceWhatsApp::class,
        ],
        PaymentCompleted::class => [
            SendPaymentWhatsApp::class,
        ],
    ];
}
