<?php

namespace App\Helpers;

/**
 * Disabled for POSH module — CRM activity_logs not used.
 */
class ActivityLogger
{
    public static function log($params = []): void
    {
        // no-op
    }
}
