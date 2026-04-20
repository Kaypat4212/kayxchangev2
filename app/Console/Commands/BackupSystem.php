<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupSystem extends Command
{
    protected $signature   = 'backup:run {--notify : Send backup to admin via Telegram and email}';
    protected $description = 'Create a full DB + storage backup, optionally notify admin';

    public function handle(): int
    {
        $this->info('Starting KayXchange backup…');

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // ── 1. DB dump ───────────────────────────────────────────────────
        $dbPath = "{$backupDir}/db_{$timestamp}.sql";
        $this->info('Dumping database…');
        $dbDumped = $this->dumpDatabase($dbPath);

        if (!$dbDumped) {
            $this->error('Database dump failed.');
            return self::FAILURE;
        }

        // ── 2. Zip everything ─────────────────────────────────────────────
        $zipPath = "{$backupDir}/kayxchange_backup_{$timestamp}.zip";
        $this->info('Creating zip archive…');

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Could not create zip file.');
            return self::FAILURE;
        }

        // Add DB dump
        $zip->addFile($dbPath, "db_{$timestamp}.sql");

        // Add storage/app/public (user uploads)
        $storagePath = storage_path('app/public');
        if (is_dir($storagePath)) {
            $this->addDirToZip($zip, $storagePath, 'storage');
        }

        $zip->close();

        // Clean up raw SQL file
        @unlink($dbPath);

        $sizeMb = round(filesize($zipPath) / 1024 / 1024, 2);
        $this->info("Backup created: {$zipPath} ({$sizeMb} MB)");

        // ── 3. Keep only last 7 backups ───────────────────────────────────
        $this->pruneOldBackups($backupDir);

        // ── 4. Notify admin ───────────────────────────────────────────────
        if ($this->option('notify')) {
            $this->notifyTelegram($zipPath, $sizeMb, $timestamp);
            $this->notifyEmail($zipPath, $sizeMb, $timestamp);
        }

        $this->info('Backup completed successfully.');
        return self::SUCCESS;
    }

    // ── DB dump via mysqldump ─────────────────────────────────────────────
    private function dumpDatabase(string $outPath): bool
    {
        $host     = config('database.connections.mysql.host', '127.0.0.1');
        $port     = config('database.connections.mysql.port', '3306');
        $db       = config('database.connections.mysql.database');
        $user     = config('database.connections.mysql.username');
        $pass     = config('database.connections.mysql.password');

        // Build command — pass password via env var to avoid shell history leak
        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --single-transaction --routines --triggers %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($db),
            escapeshellarg($outPath)
        );

        $env = "MYSQL_PWD=" . escapeshellarg($pass);

        // Windows fallback (XAMPP): use MYSQL_PWD or -p inline
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($user),
                escapeshellarg($pass),
                escapeshellarg($db),
                escapeshellarg($outPath)
            );
            exec($cmd, $output, $exitCode);
        } else {
            exec("{$env} {$cmd}", $output, $exitCode);
        }

        if ($exitCode !== 0) {
            Log::error('BackupSystem: mysqldump failed', ['output' => implode("\n", $output)]);
            return false;
        }

        return file_exists($outPath) && filesize($outPath) > 100;
    }

    // ── Recursively add directory to zip ─────────────────────────────────
    private function addDirToZip(ZipArchive $zip, string $dir, string $prefix): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            if ($file->isFile()) {
                $local = $prefix . '/' . ltrim(str_replace($dir, '', $file->getRealPath()), '/\\');
                $zip->addFile($file->getRealPath(), $local);
            }
        }
    }

    // ── Keep only the 7 most recent backup zips ───────────────────────────
    private function pruneOldBackups(string $dir): void
    {
        $files = glob("{$dir}/kayxchange_backup_*.zip");
        if (!$files) return;
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
        foreach (array_slice($files, 7) as $old) {
            @unlink($old);
            $this->info("Pruned old backup: " . basename($old));
        }
    }

    // ── Telegram notification (document upload) ───────────────────────────
    private function notifyTelegram(string $zipPath, float $sizeMb, string $timestamp): void
    {
        $token  = config('services.telegram.bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        $chatId = $this->getAdminChatId();

        if (!$token || !$chatId) {
            $this->warn('Telegram not configured — skipping Telegram notify.');
            return;
        }

        // Send text summary first
        $caption = "🗄️ *KayXchange Backup Complete*\n\n"
            . "📅 Date: {$timestamp}\n"
            . "📦 Size: {$sizeMb} MB\n"
            . "✅ Contains: DB dump + user uploads\n\n"
            . "_Backup retained for 7 days on server._";

        // Telegram has 50MB limit for bots — send summary only if file too big
        if (filesize($zipPath) <= 48 * 1024 * 1024) {
            $response = Http::attach('document', fopen($zipPath, 'r'), basename($zipPath))
                ->post("https://api.telegram.org/bot{$token}/sendDocument", [
                    'chat_id'    => $chatId,
                    'caption'    => $caption,
                    'parse_mode' => 'Markdown',
                ]);

            if ($response->successful()) {
                $this->info('Backup sent to Telegram.');
                return;
            }
        }

        // Fallback: text only
        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id'    => $chatId,
            'text'       => $caption . "\n\n⚠️ File too large to attach directly — download from admin panel.",
            'parse_mode' => 'Markdown',
        ]);
        $this->info('Telegram notification sent (text only — file too large).');
    }

    // ── Email notification ─────────────────────────────────────────────────
    private function notifyEmail(string $zipPath, float $sizeMb, string $timestamp): void
    {
        $adminEmail = User::where('is_admin', true)->value('email');
        if (!$adminEmail) {
            $this->warn('No admin email found — skipping email notify.');
            return;
        }

        try {
            Mail::send([], [], function ($message) use ($adminEmail, $zipPath, $sizeMb, $timestamp) {
                $message->to($adminEmail)
                    ->subject("✅ KayXchange Backup — {$timestamp}")
                    ->html(
                        "<h2>KayXchange Backup Complete</h2>"
                        . "<p><strong>Date:</strong> {$timestamp}<br>"
                        . "<strong>Size:</strong> {$sizeMb} MB<br>"
                        . "<strong>Contents:</strong> Full DB dump + user uploads</p>"
                        . "<p>The backup file is stored on the server. "
                        . "You can also download it from the <strong>Admin → Backup</strong> panel.</p>"
                        . "<p style='color:#888;font-size:12px'>Note: Due to email attachment size limits, the backup is not directly attached. Download from the admin panel.</p>"
                    );
            });
            $this->info("Email notification sent to {$adminEmail}.");
        } catch (\Throwable $e) {
            $this->warn('Email notification failed: ' . $e->getMessage());
            Log::warning('BackupSystem email failed: ' . $e->getMessage());
        }
    }

    private function getAdminChatId(): ?string
    {
        $envId = env('TELEGRAM_OWNER_CHAT_ID') ?: env('KAYXCHANGE_TELEGRAM_CHAT_ID') ?: env('TELEGRAM_CHAT_ID');
        if ($envId) return (string) $envId;
        return User::where('is_admin', true)->whereNotNull('telegram_chat_id')->value('telegram_chat_id');
    }
}
