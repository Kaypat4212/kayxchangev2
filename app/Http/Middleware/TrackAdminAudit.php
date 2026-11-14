<?php

namespace App\Http\Middleware;

use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;

class TrackAdminAudit
{
    public function handle(Request $request, Closure $next): Response
    {
        $subject = collect($request->route()?->parameters() ?? [])->first(fn ($value) => $value instanceof Model);
        $before = $subject ? $this->safeModelSnapshot($subject) : null;
        $response = $next($request);
        $admin = $request->user();
        if (!$admin?->is_admin || !$request->isMethod('GET') && !$request->isMethod('HEAD') && !$request->isMethod('OPTIONS')) {
            if ($admin?->is_admin && Schema::hasTable('admin_audit_logs')) {
                AdminAuditLog::create([
                    'admin_id' => $admin->id,
                    'action' => strtoupper($request->method()) . ' ' . '/' . ltrim($request->path(), '/'),
                    'subject_type' => $request->route()?->getName(),
                    'subject_id' => $subject?->getKey(),
                    'before' => $before,
                    'after' => [
                        'input_values' => $this->safeInput($request->except(['_token', '_method'])),
                        'subject' => $subject ? $this->safeModelSnapshot($subject) : null,
                        'response_status' => $response->getStatusCode(),
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                ]);
            }
        }

        $successfulMutation = $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
        if ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400) {
            $successfulMutation = $request->session()->has('success') && !$request->session()->has('errors');
        }
        if ($admin?->is_admin && $successfulMutation && $this->isSensitiveAdminMutation($request)) {
            try {
                app(\App\Services\AdminSecurityAlertService::class)->criticalAdminChange(
                    $admin,
                    $this->sensitiveArea($request),
                    strtoupper($request->method()) . ' /' . ltrim($request->path(), '/')
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }
        return $response;
    }

    private function isSensitiveAdminMutation(Request $request): bool
    {
        if ($request->isMethod('GET') || $request->isMethod('HEAD') || $request->isMethod('OPTIONS')) return false;

        // General settings changes are reported by AdminSettingsController with
        // the exact changed setting names; avoid duplicate notifications here.
        if (preg_match('#^admin/settings(?:/|$)#', $request->path())) return false;

        return (bool) preg_match(
            '#^admin/(wallet-rates|bill-providers|features|crypto-deposit-settings|env-editor|profile/(?:password|email|2fa)|users(?:/|$)|gift-card-rates|manual-crypto-deposits|trades(?:/|$)|buy-trades(?:/|$)|sell-trades(?:/|$)|withdrawals(?:/|$)|deposits(?:/|$)|crypto-withdrawals(?:/|$))#',
            $request->path()
        );
    }

    private function sensitiveArea(Request $request): string
    {
        $parts = explode('/', trim($request->path(), '/'));
        $area = $parts[1] ?? 'admin';
        return ucwords(str_replace(['-', '_'], ' ', $area));
    }

    private function safeInput(array $input): array
    {
        $safe = [];
        foreach ($input as $key => $value) {
            if (preg_match('/password|pin|secret|token|api.?key|credential|document|proof/i', (string) $key)) {
                $safe[$key] = '[redacted]';
            } elseif (!preg_match('/status|rate|fee|amount|limit|enabled|active|network|currency|address|wallet|provider|threshold|risk|category|is_admin|role|kyc/i', (string) $key)) {
                $safe[$key] = '[value omitted]';
            } elseif (preg_match('/address|wallet/i', (string) $key) && is_string($value)) {
                $safe[$key] = $this->mask($value);
            } elseif (is_scalar($value) || $value === null) {
                $safe[$key] = is_string($value) ? mb_substr($value, 0, 300) : $value;
            } else {
                $safe[$key] = '[structured value omitted]';
            }
        }
        return $safe;
    }

    private function safeModelSnapshot(Model $model): array
    {
        try { $model->refresh(); } catch (\Throwable) { return []; }
        $snapshot = [];
        foreach ($model->getAttributes() as $key => $value) {
            if (preg_match('/password|pin|secret|token|api.?key|credential|document|proof/i', (string) $key)) continue;
            if (!preg_match('/status|rate|fee|amount|limit|enabled|active|network|currency|address|wallet|provider|threshold|risk|category|balance|is_admin|role|kyc/i', (string) $key)) continue;
            if (preg_match('/address|wallet/i', (string) $key) && is_string($value)) $value = $this->mask($value);
            if (is_scalar($value) || $value === null) $snapshot[$key] = is_string($value) ? mb_substr($value, 0, 300) : $value;
        }
        return $snapshot;
    }

    private function mask(string $value): string
    {
        $value = trim($value);
        return strlen($value) <= 12 ? str_repeat('*', strlen($value)) : substr($value, 0, 5) . '…' . substr($value, -5);
    }
}
