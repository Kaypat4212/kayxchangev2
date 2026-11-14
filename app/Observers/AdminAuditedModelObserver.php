<?php

namespace App\Observers;

use App\Models\AdminAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AdminAuditedModelObserver
{
    public function created(Model $model): void
    {
        if ($this->isAdminRequest()) $this->write($model, 'record.created', null, $this->safeAttributes($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        if (!$this->isAdminRequest()) return;
        $before = $after = [];
        foreach (array_keys($model->getChanges()) as $key) {
            $before[$key] = $model->getOriginal($key);
            $after[$key] = $model->getAttribute($key);
        }
        $this->write($model, 'record.updated', $this->safeAttributes($before), $this->safeAttributes($after));
    }

    public function deleted(Model $model): void
    {
        if ($this->isAdminRequest()) $this->write($model, 'record.deleted', $this->safeAttributes($model->getAttributes()), null);
    }

    private function isAdminRequest(): bool
    {
        return (bool) (auth()->user()?->is_admin) && Schema::hasTable('admin_audit_logs');
    }

    private function write(Model $model, string $action, ?array $before, ?array $after): void
    {
        AdminAuditLog::create([
            'admin_id' => auth()->id(), 'action' => $action,
            'subject_type' => $model::class, 'subject_id' => (string) $model->getKey(),
            'before' => $before, 'after' => $after,
            'ip_address' => request()->ip(), 'user_agent' => substr((string) request()->userAgent(), 0, 1000),
        ]);
    }

    private function safeAttributes(array $attributes): array
    {
        $safe = [];
        foreach ($attributes as $key => $value) {
            if (preg_match('/password|pin|secret|token|api.?key|credential|document|proof|account_number|bank_account/i', (string) $key)) continue;
            if (!preg_match('/status|rate|fee|amount|limit|enabled|active|network|currency|address|wallet|provider|threshold|risk|category|balance|is_admin|role|kyc|reference|name|email/i', (string) $key)) continue;
            if (preg_match('/address|wallet/i', (string) $key) && is_string($value)) {
                $value = strlen($value) <= 12 ? str_repeat('*', strlen($value)) : substr($value, 0, 5) . '…' . substr($value, -5);
            } elseif (!is_scalar($value) && $value !== null) {
                $value = '[structured value omitted]';
            } elseif (is_string($value)) {
                $value = mb_substr($value, 0, 300);
            }
            $safe[$key] = $value;
        }
        return $safe;
    }
}
