<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Models\AdminSetting;
use Throwable;

class Handler extends ExceptionHandler
{
    /** Select exception detail level from the admin-controlled runtime mode. */
    public function render($request, Throwable $e)
    {
        try {
            $fallback = config('app.env') === 'local' ? 'local' : 'production';
            $mode = (string) AdminSetting::getSetting('runtime_mode', $fallback);
        } catch (\Throwable) {
            $mode = 'production';
        }

        $previousDebug = (bool) config('app.debug', false);
        $isAdmin = $request->user() && (bool) $request->user()->is_admin;
        config(['app.debug' => $mode === 'local' && $isAdmin]);
        try {
            return parent::render($request, $e);
        } finally {
            // Avoid leaking local debug state between requests in long-lived workers.
            config(['app.debug' => $previousDebug]);
        }
    }

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'vtpass_api_key',
        'vtpass_public_key',
        'vtpass_secret_key',
        'monnify_api_key',
        'monnify_secret_key',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
