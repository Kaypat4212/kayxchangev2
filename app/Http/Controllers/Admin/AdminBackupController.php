<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminBackupController extends Controller
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
    }

    public function index()
    {
        $backups = $this->listBackups();
        return view('admin.backup', compact('backups'));
    }

    public function run(Request $request)
    {
        try {
            $notify = $request->boolean('notify', true);
            Artisan::call('backup:run', $notify ? ['--notify' => true] : []);
            $output = Artisan::output();
            return back()->with('success', 'Backup completed successfully.' . ($notify ? ' Admin notified via Telegram & email.' : ''));
        } catch (\Throwable $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        // Sanitise: only allow the expected filename format
        if (!preg_match('/^kayxchange_backup_[\d_-]+\.zip$/', $filename)) {
            abort(404);
        }

        $path = $this->backupDir . '/' . $filename;
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    public function delete(string $filename)
    {
        if (!preg_match('/^kayxchange_backup_[\d_-]+\.zip$/', $filename)) {
            abort(404);
        }

        $path = $this->backupDir . '/' . $filename;
        if (file_exists($path)) {
            @unlink($path);
        }

        return back()->with('success', 'Backup deleted.');
    }

    private function listBackups(): array
    {
        if (!is_dir($this->backupDir)) {
            return [];
        }

        $files = glob($this->backupDir . '/kayxchange_backup_*.zip') ?: [];
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        return array_map(function ($path) {
            return [
                'filename' => basename($path),
                'size_mb'  => round(filesize($path) / 1024 / 1024, 2),
                'date'     => date('D, d M Y H:i:s', filemtime($path)),
                'age_days' => floor((time() - filemtime($path)) / 86400),
            ];
        }, $files);
    }
}
