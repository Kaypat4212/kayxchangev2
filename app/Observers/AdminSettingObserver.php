<?php

namespace App\Observers;

use App\Models\AdminAuditLog;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\Schema;

class AdminSettingObserver
{
    public function created(AdminSetting $setting): void
    {
        $this->record($setting, null, $setting->value, 'setting.created');
    }

    public function updated(AdminSetting $setting): void
    {
        if (!Schema::hasTable('admin_audit_logs') || !$setting->wasChanged('value')) return;
        $this->record($setting, $setting->getOriginal('value'), $setting->value, 'setting.updated');
    }

    private function record(AdminSetting $setting, mixed $before, mixed $after, string $action): void
    {
        if (!Schema::hasTable('admin_audit_logs')) return;
        $sensitive = (bool) $setting->is_encrypted;
        AdminAuditLog::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'subject_type' => 'admin_setting',
            'subject_id' => (string) $setting->key,
            'before' => $before === null ? null : ['value' => $sensitive ? '[redacted]' : $before],
            'after' => ['value' => $sensitive ? '[redacted]' : $after],
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 1000),
        ]);
    }
}
