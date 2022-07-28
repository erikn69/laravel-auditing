<?php

namespace OwenIt\Auditing;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use OwenIt\Auditing\Events\AuditCustom;
use OwenIt\Auditing\Listeners\RecordCustomAudit;

class AuditingLumenEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AuditCustom::class => [
            RecordCustomAudit::class,
        ]
    ];
}
