<?php

namespace OwenIt\Auditing\Drivers;

use OwenIt\Auditing\Contracts\Audit;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\AuditDriver;

class Database implements AuditDriver
{
    /**
     * {@inheritdoc}
     */
    public function audit(Auditable $model): ?Audit
    {
        return call_user_func([get_class($model->audits()->getModel()), 'create'], $model->toAudit());
    }

    /**
     * {@inheritdoc}
     */
    public function prune(Auditable $model): bool
    {
        if (($threshold = $model->getAuditThreshold()) > 0) {
            $auditClass = get_class($model->audits()->getModel());
            $keyName = (new $auditClass)->getKeyName();
            $forRemoval = array_slice(
                $model->audits()->latest()->pluck($keyName)->all(),
                $threshold
            );

            if (count($forRemoval)) {
                return $model->audits()
                    ->whereIntegerInRaw($keyName, $forRemoval)
                    ->delete() > 0;
            }
        }

        return false;
    }
}
