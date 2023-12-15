<?php

namespace OwenIt\Auditing\Tests\Unit;

use Illuminate\Support\Facades\Event;
use OwenIt\Auditing\AuditableObserver;
use OwenIt\Auditing\Events\DispatchAudit;
use OwenIt\Auditing\Events\DispatchingAudit;
use OwenIt\Auditing\Models\Audit;
use OwenIt\Auditing\Tests\AuditingTestCase;
use OwenIt\Auditing\Tests\Models\Article;

class AuditableObserverTest extends AuditingTestCase
{
    /**
     * @test
     *
     * @dataProvider auditableObserverDispatchTestProvider
     */
    public function itWillCancelTheAuditDispatchingFromAnEventListener($eventMethod)
    {
        Event::fake(
            [
                DispatchAudit::class,
            ]
        );

        Event::listen(DispatchingAudit::class, function () {
            return false;
        });

        $observer = new AuditableObserver();
        $model = Article::factory()->create();

        $observer->$eventMethod($model);

        $this->assertNull(Audit::first());

        Event::assertNotDispatched(DispatchAudit::class);
    }

    /**
     * @test
     *
     * @dataProvider auditableObserverDispatchTestProvider
     */
    public function itDispatchesTheCorrectEvents(string $eventMethod)
    {
        Event::fake();

        $observer = new AuditableObserver();
        $model = Article::factory()->create();

        $observer->$eventMethod($model);

        Event::assertDispatched(DispatchingAudit::class, function ($event) use ($model) {
            return $event->model->is($model);
        });

        Event::assertDispatched(DispatchAudit::class, function ($event) use ($model) {
            return $event->model->is($model);
        });
    }

    /**
     * @group AuditableObserver::retrieved
     * @group AuditableObserver::created
     * @group AuditableObserver::updated
     * @group AuditableObserver::deleted
     * @group AuditableObserver::restoring
     * @group AuditableObserver::restored
     *
     * @test
     *
     * @dataProvider auditableObserverTestProvider
     */
    public function itExecutesTheAuditorSuccessfully(string $eventMethod, bool $expectedBefore, bool $expectedAfter)
    {
        $observer = new AuditableObserver();
        $model = Article::factory()->create();

        $this->assertSame($expectedBefore, $observer::$restoring);

        $observer->$eventMethod($model);

        $this->assertSame($expectedAfter, $observer::$restoring);
    }

    public function auditableObserverTestProvider(): array
    {
        return [
            [
                'retrieved',
                false,
                false,
            ],
            [
                'created',
                false,
                false,
            ],
            [
                'updated',
                false,
                false,
            ],
            [
                'deleted',
                false,
                false,
            ],
            [
                'restoring',
                false,
                true,
            ],
            [
                'restored',
                true,
                false,
            ],
        ];
    }

    public function auditableObserverDispatchTestProvider(): array
    {
        return [
            [
                'created',
            ],
            [
                'updated',
            ],
            [
                'deleted',
            ],
            [
                'restored',
            ],
        ];
    }
}
