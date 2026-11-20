<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ToolsController extends Controller
{
    public function bin(Request $request): JsonResponse
    {
        $bin = (string) $request->query('bin', '');
        abort_unless(preg_match('/\A[0-9]{6,8}\z/', $bin), 422, 'Enter exactly 6 to 8 digits from the card prefix. Do not enter a full card number.');

        $data = Cache::remember("tools:bin:{$bin}", now()->addHours(6), function () use ($bin) {
            $notFound = false;
            $rateLimited = false;
            $reachable = false;
            $providerFailed = false;

            // The public Binlist endpoint is heavily throttled. Try a broader
            // multi-country index first, then fall back to Binlist if needed.
            $providers = [
                function () use ($bin) {
                    $request = Http::acceptJson()->timeout(7);
                    $apiKey = trim((string) AdminSetting::getSetting('cardbincheck_api_key', ''))
                        ?: (string) config('services.cardbincheck.api_key', '');
                    if ($apiKey !== '') {
                        $request = $request->withHeaders(['X-Api-Key' => $apiKey]);
                    }
                    $response = $request->get("https://cardbincheck.com/api/bin/{$bin}");
                    if (!$response->successful() || !$response->json('success')) {
                        return ['status' => $response->status(), 'data' => null];
                    }
                    $record = $response->json('data', []);
                    return ['status' => 200, 'data' => [
                        'scheme' => data_get($record, 'scheme'),
                        'type' => data_get($record, 'funding'),
                        'brand' => data_get($record, 'segment'),
                        'bank' => ['name' => data_get($record, 'issuer')],
                        'country' => ['name' => data_get($record, 'country_name'), 'alpha2' => data_get($record, 'country_code')],
                        'matched_bin' => data_get($record, 'bin'),
                        'provider' => 'CardBinCheck',
                    ]];
                },
                function () use ($bin) {
                    $response = Http::acceptJson()->withHeaders(['Accept-Version' => '3'])->timeout(7)->get("https://lookup.binlist.net/{$bin}");
                    if (!$response->successful()) {
                        return ['status' => $response->status(), 'data' => null];
                    }
                    $record = $response->json();
                    return ['status' => 200, 'data' => [
                        'scheme' => data_get($record, 'scheme'),
                        'type' => data_get($record, 'type'),
                        'brand' => data_get($record, 'brand'),
                        'bank' => ['name' => data_get($record, 'bank.name')],
                        'country' => ['name' => data_get($record, 'country.name'), 'alpha2' => data_get($record, 'country.alpha2')],
                        'matched_bin' => $bin,
                        'provider' => 'Binlist',
                    ]];
                },
            ];

            // Optional authenticated dataset for improved international
            // matching. Keep its key in server environment only.
            $apiKey = (string) config('services.binlookup.api_key', '');
            if ($apiKey !== '') {
                array_unshift($providers, function () use ($bin, $apiKey) {
                    $response = Http::acceptJson()->withToken($apiKey)->timeout(7)
                        ->post('https://api.binlookupapi.com/v1/bin', ['number' => $bin]);
                    if (!$response->successful()) {
                        return ['status' => $response->status(), 'data' => null];
                    }
                    $record = $response->json('data', []);
                    return ['status' => 200, 'data' => [
                        'scheme' => data_get($record, 'scheme'),
                        'type' => data_get($record, 'funding'),
                        'brand' => data_get($record, 'brand') ?: data_get($record, 'category'),
                        'bank' => ['name' => data_get($record, 'issuer.name')],
                        'country' => ['name' => data_get($record, 'country.name'), 'alpha2' => data_get($record, 'country.code')],
                        'matched_bin' => data_get($record, 'bin'),
                        'provider' => 'BINLookupAPI',
                    ]];
                });
            }

            foreach ($providers as $provider) {
                try {
                    $result = $provider();
                    $reachable = true;
                    if ($result['data']) return $result['data'];
                    if ($result['status'] === 404) $notFound = true;
                    if ($result['status'] === 429) $rateLimited = true;
                    if (!in_array($result['status'], [200, 404, 429], true)) $providerFailed = true;
                } catch (\Illuminate\Http\Client\ConnectionException) {
                    // Try the next dataset; do not expose provider diagnostics.
                    $providerFailed = true;
                }
            }

            if ($notFound && !$rateLimited && !$providerFailed) abort(404, 'No issuer record was found for this prefix in the available datasets. Some local bank BIN ranges may not be indexed yet.');
            if ($rateLimited) abort(429, 'BIN lookup providers are rate-limited. Wait a little and try again.');
            abort(502, $reachable ? 'BIN lookup providers are temporarily unavailable.' : 'BIN lookup providers could not be reached. Try again later.');
        });
        return response()->json($data);
    }

    public function username(Request $request): JsonResponse
    {
        $service = (string) $request->query('service', '');
        $username = (string) $request->query('username', '');
        abort_unless(in_array($service, ['github', 'npm'], true) && preg_match('/^[a-z\d](?:[a-z\d-]{0,37}[a-z\d])?$/i', $username), 422, 'Invalid service or username.');

        $data = Cache::remember("tools:username:{$service}:" . strtolower($username), now()->addMinutes(5), function () use ($service, $username) {
            $url = $service === 'github'
                ? 'https://api.github.com/users/' . rawurlencode($username)
                : 'https://registry.npmjs.org/' . rawurlencode($username);
            try {
                $response = Http::acceptJson()->timeout(8)->get($url);
            } catch (\Illuminate\Http\Client\ConnectionException $exception) {
                abort(502, 'The username provider could not be reached. Try again later.');
            }
            if ($response->status() === 404) return ['exists' => false, 'service' => $service, 'username' => $username];
            if ($response->status() === 403) abort(429, 'The provider rate limit was reached. Try again later.');
            if (!$response->successful()) abort(502, 'The username provider is temporarily unavailable.');

            return ['exists' => true, 'service' => $service, 'username' => $service === 'github' ? $response->json('login', $username) : $response->json('name', $username), 'display_name' => $service === 'github' ? $response->json('name') : null];
        });
        return response()->json($data);
    }

    public function dns(Request $request): JsonResponse
    {
        $name = strtolower(rtrim((string) $request->query('name', ''), '.'));
        $type = strtoupper((string) $request->query('type', 'A'));
        abort_unless(preg_match('/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i', $name), 422, 'Enter a valid domain or hostname.');
        abort_unless(in_array($type, ['A','AAAA','MX','TXT','NS','CNAME'], true), 422, 'Unsupported DNS record type.');

        $data = Cache::remember("tools:dns:{$name}:{$type}", now()->addMinutes(5), function () use ($name, $type) {
            try {
                $response = Http::acceptJson()->timeout(8)->get('https://dns.google/resolve', ['name' => $name, 'type' => $type]);
            } catch (\Illuminate\Http\Client\ConnectionException $exception) {
                abort(502, 'The DNS provider could not be reached. Try again later.');
            }
            if (!$response->successful()) abort(502, 'The DNS provider is temporarily unavailable.');
            $expected = ['A'=>1,'NS'=>2,'CNAME'=>5,'MX'=>15,'TXT'=>16,'AAAA'=>28][$type];
            return ['status' => $response->json('Status'), 'comment' => $response->json('Comment'), 'answers' => collect($response->json('Answer', []))->where('type', $expected)->map(fn ($answer) => ['type' => $answer['type'], 'ttl' => $answer['TTL'] ?? null, 'data' => $answer['data'] ?? ''])->values()->all()];
        });
        return response()->json($data);
    }

    /** Probe SMTP reachability and TLS authentication without sending or logging credentials. */
    public function smtpProbe(Request $request): JsonResponse
    {
        $input = $request->request->all();
        $validator = Validator::make($input, [
            'host' => ['required', 'string', 'max:253', 'regex:/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i'],
            'port' => ['required', 'integer', 'in:25,465,587,2525'],
            'encryption' => ['required', 'in:tls,ssl'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'password' => ['required', 'string', 'min:1', 'max:1024'],
        ]);
        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $request->request->remove('email');
            $request->request->remove('password');
            unset($validator, $input);
            return response()->json(['message' => $message], 422);
        }

        $validated = $validator->validated();
        $host = strtolower($validated['host']);
        $port = (int) $validated['port'];
        $encryption = $validated['encryption'];
        $email = (string) $validated['email'];
        $password = (string) $validated['password'];
        $request->request->remove('email');
        $request->request->remove('password');
        if ($request->isJson()) {
            $request->json()->remove('email');
            $request->json()->remove('password');
        }
        unset($validated['email'], $validated['password'], $input['email'], $input['password']);
        unset($validator, $input, $validated);

        if (($port === 465 && $encryption !== 'ssl') || ($port !== 465 && $encryption === 'ssl')) {
            $password = str_repeat("\0", strlen($password));
            unset($email, $password);
            return response()->json(['message' => 'Use implicit TLS on port 465 and STARTTLS on ports 25, 587, or 2525.'], 422);
        }

        $records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];
        $addresses = [];
        foreach ($records as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $addresses[] = $ip;
            }
        }
        if (!$addresses) {
            $password = str_repeat("\0", strlen($password));
            unset($email, $password);
            return response()->json(['message' => 'The host did not resolve to a public IP address. Check the hostname and DNS records.'], 422);
        }

        $ip = $addresses[0];
        $remote = str_contains($ip, ':') ? "[{$ip}]" : $ip;
        $scheme = $encryption === 'ssl' ? 'ssl' : 'tcp';
        $context = stream_context_create(['ssl' => [
            'peer_name' => $host,
            'verify_peer' => true,
            'verify_peer_name' => true,
            'SNI_enabled' => true,
            'allow_self_signed' => false,
        ]]);
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client("{$scheme}://{$remote}:{$port}", $errno, $errstr, 7, STREAM_CLIENT_CONNECT, $context);
        if (!is_resource($socket)) {
            $password = str_repeat("\0", strlen($password));
            unset($email, $password);
            return response()->json(['message' => 'Could not connect to that SMTP host and port. Confirm the host, port, firewall, and hosting provider rules.'], 502);
        }

        stream_set_timeout($socket, 7);
        $authPayload = '';
        try {
            [$code, $reply] = $this->readSmtpReply($socket);
            if ($code !== 220) {
                return response()->json(['message' => 'The server connected but did not return the expected SMTP greeting.'], 502);
            }
            @fwrite($socket, "EHLO tradewithkay.com\r\n");
            [$code, $reply] = $this->readSmtpReply($socket);
            if ($code !== 250) {
                return response()->json(['message' => 'The server answered, but rejected the SMTP greeting command.'], 502);
            }

            if ($encryption === 'tls') {
                if (!str_contains(strtoupper($reply), 'STARTTLS')) {
                    return response()->json(['message' => 'The SMTP server does not advertise STARTTLS on this port.'], 422);
                }
                @fwrite($socket, "STARTTLS\r\n");
                [$code, $reply] = $this->readSmtpReply($socket);
                if ($code !== 220 || @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
                    return response()->json(['message' => 'STARTTLS was requested, but the encrypted handshake failed. Check the port and certificate.'], 502);
                }
                @fwrite($socket, "EHLO tradewithkay.com\r\n");
                [$code, $reply] = $this->readSmtpReply($socket);
                if ($code !== 250) {
                    return response()->json(['message' => 'TLS connected, but the SMTP greeting failed after encryption.'], 502);
                }
            }

            $capabilities = strtoupper($reply);
            $authMode = preg_match('/AUTH(?:=|\s+)[^\r\n]*\bLOGIN\b/', $capabilities) ? 'login' : (preg_match('/AUTH(?:=|\s+)[^\r\n]*\bPLAIN\b/', $capabilities) ? 'plain' : null);
            if (!$authMode) {
                return response()->json(['message' => 'TLS connected, but this server did not advertise SMTP AUTH LOGIN or PLAIN.'], 422);
            }

            if ($authMode === 'login') {
                @fwrite($socket, "AUTH LOGIN\r\n");
                [$code] = $this->readSmtpReply($socket);
                if ($code !== 334) {
                    return response()->json(['message' => 'The SMTP server rejected the authentication test. Check its SMTP AUTH settings.'], 502);
                }
                @fwrite($socket, base64_encode($email) . "\r\n");
                [$code] = $this->readSmtpReply($socket);
                if ($code !== 334) {
                    return response()->json(['message' => 'The SMTP server rejected the email address. Check the account name and SMTP AUTH settings.'], 401);
                }
                $authPayload = base64_encode($password);
                @fwrite($socket, $authPayload . "\r\n");
            } else {
                $authPayload = base64_encode("\0{$email}\0{$password}");
                @fwrite($socket, 'AUTH PLAIN ' . $authPayload . "\r\n");
            }
            [$code] = $this->readSmtpReply($socket);
            if ($code === 334 && $authMode === 'plain') {
                @fwrite($socket, $authPayload . "\r\n");
                [$code] = $this->readSmtpReply($socket);
            }
            if ($code !== 235) {
                return response()->json(['message' => $code === 535
                    ? 'The SMTP server rejected these credentials. Check the email, password, and provider app-password requirements.'
                    : 'The SMTP server could not complete authentication. Check its SMTP AUTH settings and try again.'], 401);
            }

            @fwrite($socket, "QUIT\r\n");
            return response()->json([
                'ok' => true,
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption,
                'message' => 'SMTP connection, TLS, and authentication succeeded. No email was sent.',
            ]);
        } catch (\Throwable $exception) {
            return response()->json(['message' => 'The SMTP server closed the connection before the check completed. Confirm the host and selected security mode.'], 502);
        } finally {
            $password = str_repeat("\0", strlen($password));
            $authPayload = str_repeat("\0", strlen($authPayload));
            unset($email, $password, $authPayload, $input, $validated);
            if (is_resource($socket)) {
                fclose($socket);
            }
        }
    }

    /** Read one SMTP response, including any continuation lines. */
    private function readSmtpReply($socket): array
    {
        $lines = [];
        $code = 0;
        for ($i = 0; $i < 20; $i++) {
            $line = fgets($socket, 2048);
            if ($line === false) {
                break;
            }
            $lines[] = trim($line);
            if (preg_match('/^(\d{3})([ -])/', $line, $matches)) {
                $code = (int) $matches[1];
                if ($matches[2] === ' ') {
                    break;
                }
            } else {
                break;
            }
        }

        return [$code, substr(strip_tags(implode(' ', $lines)), 0, 300)];
    }

    public function githubRepository(Request $request): JsonResponse
    {
        $repo = trim((string) $request->query('repo', ''));
        abort_unless(preg_match('/^[a-z\d](?:[a-z\d-]{0,37}[a-z\d])?\/[a-z\d._-]{1,100}$/i', $repo), 422, 'Use the format owner/repository.');

        $data = Cache::remember('tools:github-repo:' . strtolower($repo), now()->addMinutes(5), function () use ($repo) {
            [$owner, $name] = explode('/', $repo, 2);
            try {
                $response = Http::accept('application/vnd.github+json')->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])->timeout(8)->get('https://api.github.com/repos/' . rawurlencode($owner) . '/' . rawurlencode($name));
            } catch (\Illuminate\Http\Client\ConnectionException $exception) {
                abort(502, 'GitHub could not be reached. Try again later.');
            }
            if ($response->status() === 404) abort(404, 'Public repository not found. Check the owner and repository name.');
            if ($response->status() === 403) abort(429, 'GitHub rate limit reached. Wait a minute and try again.');
            if (!$response->successful()) abort(502, 'GitHub is temporarily unavailable.');
            $data = $response->json();
            return collect($data)->only(['full_name','description','stargazers_count','forks_count','language','updated_at','html_url'])->all();
        });
        return response()->json($data);
    }
}
