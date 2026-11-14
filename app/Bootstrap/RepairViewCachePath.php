<?php

namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

/** Repairs a missing/empty compiled-view path before any package boots Blade. */
class RepairViewCachePath
{
    public function bootstrap(Application $app): void
    {
        $configured = $app['config']->get('view.compiled');
        $candidate = is_string($configured) && trim($configured) !== ''
            ? $configured
            : storage_path('framework/views');

        if (!is_dir($candidate)) @mkdir($candidate, 0775, true);
        if (!is_dir($candidate) || !is_writable($candidate)) {
            $candidate = storage_path('framework/views');
            if (!is_dir($candidate)) @mkdir($candidate, 0775, true);
        }

        $app['config']->set('view.compiled', $candidate);
    }
}
