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

    // ── Pure-PHP PDO database dump (no exec/shell required) ──────────────
    private function dumpDatabase(string $outPath): bool
    {
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $db   = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');

        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
                $user,
                $pass,
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                ]
            );
        } catch (\PDOException $e) {
            Log::error('BackupSystem: PDO connection failed: ' . $e->getMessage());
            return false;
        }

        try {
            $fh = fopen($outPath, 'w');
            if (!$fh) {
                Log::error('BackupSystem: Cannot open output file: ' . $outPath);
                return false;
            }

            $now = now()->toDateTimeString();
            fwrite($fh, "-- KayXchange Database Backup\n");
            fwrite($fh, "-- Generated: {$now}\n");
            fwrite($fh, "-- Database: {$db}\n\n");
            fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($fh, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
            fwrite($fh, "SET NAMES utf8mb4;\n\n");

            // Get all tables
            $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $quoted = "`{$table}`";

                // DROP + CREATE
                fwrite($fh, "-- -------------------------------------------------------\n");
                fwrite($fh, "-- Table: {$table}\n");
                fwrite($fh, "-- -------------------------------------------------------\n");
                fwrite($fh, "DROP TABLE IF EXISTS {$quoted};\n");

                $createRow = $pdo->query("SHOW CREATE TABLE {$quoted}")->fetch(\PDO::FETCH_ASSOC);
                $createSql = $createRow['Create Table'] ?? $createRow[array_key_last($createRow)];
                fwrite($fh, $createSql . ";\n\n");

                // Row count check
                $count = (int) $pdo->query("SELECT COUNT(*) FROM {$quoted}")->fetchColumn();
                if ($count === 0) {
                    continue;
                }

                // Dump rows in batches of 500
                $offset = 0;
                $batch  = 500;

                while ($offset < $count) {
                    $rows = $pdo->query("SELECT * FROM {$quoted} LIMIT {$batch} OFFSET {$offset}")->fetchAll(\PDO::FETCH_ASSOC);
                    if (empty($rows)) break;

                    $cols   = '`' . implode('`, `', array_keys($rows[0])) . '`';
                    $values = [];

                    foreach ($rows as $row) {
                        $escaped = array_map(function ($val) use ($pdo) {
                            if ($val === null) return 'NULL';
                            return $pdo->quote((string) $val);
                        }, $row);
                        $values[] = '(' . implode(', ', $escaped) . ')';
                    }

                    fwrite($fh, "INSERT INTO {$quoted} ({$cols}) VALUES\n");
                    fwrite($fh, implode(",\n", $values) . ";\n\n");

                    $offset += $batch;
                }
            }

            fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($fh);

        } catch (\Throwable $e) {
            Log::error('BackupSystem: dump error: ' . $e->getMessage());
            return false;
        }

        return file_exists($outPath) && filesize($outPath) > 50;
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
