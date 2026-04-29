<?php

namespace App\Services;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\Http;

class CloudflareService
{
    private string $apiBase = 'https://api.cloudflare.com/client/v4';
    private ?string $token;
    private ?string $zoneId;
    private ?string $accountId;

    public function __construct()
    {
        // Settings DB takes precedence over .env
        $this->token     = AdminSetting::getSetting('cf_api_token')    ?: config('services.cloudflare.token');
        $this->zoneId    = AdminSetting::getSetting('cf_zone_id')      ?: config('services.cloudflare.zone_id');
        $this->accountId = AdminSetting::getSetting('cf_account_id')   ?: config('services.cloudflare.account_id');
    }

    // ─── Zone ─────────────────────────────────────────────────────────

    public function getZoneDetails(): array
    {
        $res = $this->get("/zones/{$this->zoneId}");
        if (! ($res['success'] ?? false)) {
            return ['error' => $res['errors'][0]['message'] ?? 'Cloudflare API error'];
        }
        return $res['result'];
    }

    public function getZoneAnalytics(string $since = '-1440'): array
    {
        // since = minutes ago (e.g. -1440 = last 24h)
        $res = $this->get("/zones/{$this->zoneId}/analytics/dashboard", [
            'since'  => $since,
            'until'  => '0',
            'continuous' => true,
        ]);
        return $res['result'] ?? [];
    }

    // ─── Cache ────────────────────────────────────────────────────────

    public function purgeAllCache(): array
    {
        $res = $this->post("/zones/{$this->zoneId}/purge_cache", ['purge_everything' => true]);
        return [
            'success' => $res['success'] ?? false,
            'message' => $res['success'] ? 'All cache purged successfully.' : ($res['errors'][0]['message'] ?? 'Purge failed.'),
        ];
    }

    public function purgeUrls(array $urls): array
    {
        $urls = array_values(array_filter(array_map('trim', $urls)));
        if (empty($urls)) return ['success' => false, 'message' => 'No URLs provided.'];

        $res = $this->post("/zones/{$this->zoneId}/purge_cache", ['files' => $urls]);
        return [
            'success' => $res['success'] ?? false,
            'message' => $res['success'] ? count($urls) . ' URL(s) purged.' : ($res['errors'][0]['message'] ?? 'Purge failed.'),
        ];
    }

    // ─── Settings ─────────────────────────────────────────────────────

    public function setDevelopmentMode(bool $on): array
    {
        $res = $this->patch("/zones/{$this->zoneId}/settings/development_mode", [
            'value' => $on ? 'on' : 'off',
        ]);
        return [
            'success' => $res['success'] ?? false,
            'message' => $res['success'] ? 'Development mode ' . ($on ? 'enabled' : 'disabled') . '.' : ($res['errors'][0]['message'] ?? 'Failed.'),
        ];
    }

    public function getSecurityLevel(): string
    {
        $res = $this->get("/zones/{$this->zoneId}/settings/security_level");
        return $res['result']['value'] ?? 'unknown';
    }

    public function setSecurityLevel(string $level): array
    {
        // levels: off, essentially_off, low, medium, high, under_attack
        $res = $this->patch("/zones/{$this->zoneId}/settings/security_level", ['value' => $level]);
        return [
            'success' => $res['success'] ?? false,
            'message' => $res['success'] ? "Security level set to {$level}." : ($res['errors'][0]['message'] ?? 'Failed.'),
        ];
    }

    public function enableUnderAttackMode(): array
    {
        return $this->setSecurityLevel('under_attack');
    }

    public function disableUnderAttackMode(): array
    {
        return $this->setSecurityLevel('medium');
    }

    // ─── Firewall rules (WAF) ─────────────────────────────────────────

    public function getFirewallRules(int $perPage = 20): array
    {
        $res = $this->get("/zones/{$this->zoneId}/firewall/rules", ['per_page' => $perPage]);
        return $res['result'] ?? [];
    }

    // ─── DNS Records ──────────────────────────────────────────────────

    public function getDnsRecords(): array
    {
        $res = $this->get("/zones/{$this->zoneId}/dns_records", ['per_page' => 100]);
        return $res['result'] ?? [];
    }

    // ─── HTTP helpers ─────────────────────────────────────────────────

    private function request(string $method, string $path, array $data = []): array
    {
        if (! $this->token) return ['success' => false, 'errors' => [['message' => 'Cloudflare API token not configured.']]];

        $http = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->timeout(10);

        $url = $this->apiBase . $path;

        $response = match (strtoupper($method)) {
            'GET'   => $http->get($url, $data),
            'POST'  => $http->post($url, $data),
            'PATCH' => $http->patch($url, $data),
            'DELETE'=> $http->delete($url, $data),
            default => $http->get($url),
        };

        return $response->json() ?? ['success' => false, 'errors' => [['message' => 'Empty response']]];
    }

    private function get(string $path, array $query = []): array    { return $this->request('GET', $path, $query); }
    private function post(string $path, array $body = []): array    { return $this->request('POST', $path, $body); }
    private function patch(string $path, array $body = []): array   { return $this->request('PATCH', $path, $body); }
}
