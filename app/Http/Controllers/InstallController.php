<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Exception;

class InstallController extends Controller
{
    private $requirements = [
        'php' => '8.2.0',
        'extensions' => [
            'OpenSSL',
            'PDO',
            'pdo_mysql',
            'Mbstring',
            'Tokenizer',
            'XML',
            'Ctype',
            'JSON',
            'BCMath',
            'Curl',
            'GD',
            'Zip',
            'Fileinfo'
        ],
        'permissions' => [
            'storage/app' => '775',
            'storage/framework' => '775',
            'storage/logs' => '775',
            'bootstrap/cache' => '775',
            'public/uploads' => '775',
            'public/storage' => '775',
            '.env' => 'writable'
        ]
    ];

    /**
     * Show installation welcome page
     */
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect()->route('home')->with('error', 'Application is already installed.');
        }

        return view('install.welcome');
    }

    /**
     * Check system requirements
     */
    public function requirements()
    {
        if ($this->isInstalled()) {
            return redirect()->route('home');
        }

        $checks = [
            'php_version' => $this->checkPhpVersion(),
            'extensions' => $this->checkExtensions(),
            'permissions' => $this->checkPermissions(),
            'composer' => $this->checkComposer(),
            'node' => $this->checkNode()
        ];

        $canProceed = $checks['php_version']['status'] && 
                     $checks['extensions']['all_passed'] && 
                     $checks['permissions']['all_passed'] &&
                     $checks['composer']['status'];

        return view('install.requirements', compact('checks', 'canProceed'));
    }

    /**
     * Show database configuration form
     */
    public function database()
    {
        if ($this->isInstalled()) {
            return redirect()->route('home');
        }

        return view('install.database');
    }

    /**
     * Test and save database configuration
     */
    public function databaseStore(Request $request)
    {
        if ($this->isInstalled()) abort(404);
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_name' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            // Test database connection
            $connection = $this->testDbConnection(
                $request->db_host,
                $request->db_port,
                $request->db_name,
                $request->db_username,
                $request->db_password
            );

            if (!$connection['success']) {
                return back()->withErrors(['db_connection' => $connection['message']]);
            }

            // Update .env file
            $this->updateEnvFile([
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_name,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password,
            ]);

            return redirect()->route('install.application');

        } catch (Exception $e) {
            return back()->withErrors(['db_connection' => 'Database connection failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Show application configuration form
     */
    public function application()
    {
        if ($this->isInstalled()) {
            return redirect()->route('home');
        }

        return view('install.application');
    }

    /**
     * Store application configuration
     */
    public function applicationStore(Request $request)
    {
        if ($this->isInstalled()) abort(404);
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'admin_name' => 'required|string|max:255',
            // The users table does not exist yet on a fresh cPanel install.
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8|confirmed',
            'mail_driver' => 'required|in:smtp,sendmail,mailgun,ses,log',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|numeric',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl,null',
            'paystack_public_key' => 'nullable|string',
            'paystack_secret_key' => 'nullable|string',
            'flutterwave_public_key' => 'nullable|string|max:255',
            'flutterwave_secret_key' => 'nullable|string|max:255',
            'korapay_public_key' => 'nullable|string|max:255',
            'korapay_secret_key' => 'nullable|string|max:255',
            'groq_api_key' => 'nullable|string|max:255',
            'groq_model' => 'nullable|string|max:120',
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_chat_id' => 'nullable|string|max:120',
            'wallet_btc_address' => 'nullable|string|max:255',
            'wallet_eth_address' => 'nullable|string|max:255',
            'wallet_bnb_address' => 'nullable|string|max:255',
            'wallet_sol_address' => 'nullable|string|max:255',
            'wallet_usdt_address' => 'nullable|string|max:255',
            'wallet_usdt_trc20_address' => 'nullable|string|max:255',
            'wallet_usdt_erc20_address' => 'nullable|string|max:255',
            'wallet_usdt_bep20_address' => 'nullable|string|max:255',
            'wallet_usdc_address' => 'nullable|string|max:255',
            'wallet_usdc_erc20_address' => 'nullable|string|max:255',
            'wallet_usdc_bep20_address' => 'nullable|string|max:255',
            'support_email' => 'nullable|email|max:255',
            'company_bank_name' => 'nullable|string|max:120',
            'company_account_name' => 'nullable|string|max:120',
            'company_account_number' => 'nullable|string|max:40',
            'blockcypher_token' => 'nullable|string|max:255',
            'trongrid_api_key' => 'nullable|string|max:255',
            'etherscan_api_key' => 'nullable|string|max:255',
            'bscscan_api_key' => 'nullable|string|max:255',
            'opay_merchant_id' => 'nullable|string|max:255',
            'opay_public_key' => 'nullable|string|max:255',
            'opay_private_key' => 'nullable|string|max:255',
        ]);

        try {
            // Update .env file with application settings
            $envData = [
                'APP_NAME' => $request->app_name,
                'APP_URL' => $request->app_url,
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
                'MAIL_MAILER' => $request->mail_driver,
                'MAIL_HOST' => $request->mail_host,
                'MAIL_PORT' => $request->mail_port,
                'MAIL_USERNAME' => $request->mail_username,
                'MAIL_PASSWORD' => $request->mail_password,
                'MAIL_ENCRYPTION' => $request->mail_encryption === 'null' ? '' : $request->mail_encryption,
                'MAIL_FROM_ADDRESS' => $request->admin_email,
                'MAIL_FROM_NAME' => $request->app_name,
                'CACHE_DRIVER' => 'file',
                'SESSION_DRIVER' => 'file',
                'QUEUE_CONNECTION' => 'sync',
            ];

            $optionalEnv = [
                'TELEGRAM_BOT_TOKEN' => 'telegram_bot_token',
                'KAYXCHANGE_TELEGRAM_BOT_TOKEN' => 'telegram_bot_token',
                'KAYXCHANGE_TELEGRAM_CHAT_ID' => 'telegram_chat_id',
                'PAYSTACK_PUBLIC_KEY' => 'paystack_public_key',
                'PAYSTACK_SECRET_KEY' => 'paystack_secret_key',
                'FLUTTERWAVE_PUBLIC_KEY' => 'flutterwave_public_key',
                'FLUTTERWAVE_SECRET_KEY' => 'flutterwave_secret_key',
                'KORAPAY_PUBLIC_KEY' => 'korapay_public_key',
                'KORAPAY_SECRET_KEY' => 'korapay_secret_key',
                'GROQ_API_KEY' => 'groq_api_key',
                'GROQ_MODEL' => 'groq_model',
                'WALLET_BTC_ADDRESS' => 'wallet_btc_address',
                'WALLET_ETH_ADDRESS' => 'wallet_eth_address',
                'WALLET_BNB_ADDRESS' => 'wallet_bnb_address',
                'WALLET_SOL_ADDRESS' => 'wallet_sol_address',
                'WALLET_USDT_ADDRESS' => 'wallet_usdt_address',
                'WALLET_USDT_TRC20_ADDRESS' => 'wallet_usdt_trc20_address',
                'WALLET_USDT_ERC20_ADDRESS' => 'wallet_usdt_erc20_address',
                'WALLET_USDT_BEP20_ADDRESS' => 'wallet_usdt_bep20_address',
                'WALLET_USDC_ADDRESS' => 'wallet_usdc_address',
                'WALLET_USDC_ERC20_ADDRESS' => 'wallet_usdc_erc20_address',
                'WALLET_USDC_BEP20_ADDRESS' => 'wallet_usdc_bep20_address',
                'SUPPORT_EMAIL' => 'support_email',
                'COMPANY_BANK_NAME' => 'company_bank_name',
                'COMPANY_ACCOUNT_NAME' => 'company_account_name',
                'COMPANY_ACCOUNT_NUMBER' => 'company_account_number',
                'BLOCKCYPHER_TOKEN' => 'blockcypher_token',
                'TRONGRID_API_KEY' => 'trongrid_api_key',
                'ETHERSCAN_API_KEY' => 'etherscan_api_key',
                'BSCSCAN_API_KEY' => 'bscscan_api_key',
                'OPAY_MERCHANT_ID' => 'opay_merchant_id',
                'OPAY_PUBLIC_KEY' => 'opay_public_key',
                'OPAY_PRIVATE_KEY' => 'opay_private_key',
            ];
            foreach ($optionalEnv as $envKey => $inputKey) {
                if ($request->filled($inputKey)) {
                    $envData[$envKey] = $request->input($inputKey);
                }
            }

            $this->updateEnvFile($envData);
            File::delete(base_path('bootstrap/cache/config.php'));

            // Store admin data in session for final step
            session([
                'install_admin' => [
                    'name' => $request->admin_name,
                    'email' => $request->admin_email,
                    'password' => $request->admin_password,
                ]
            ]);

            return redirect()->route('install.final');

        } catch (Exception $e) {
            return back()->withErrors(['config' => 'Configuration failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Final installation step
     */
    public function final()
    {
        if ($this->isInstalled()) {
            return redirect()->route('home');
        }

        if (!session()->has('install_admin')) return redirect()->route('install.application');
        return view('install.final');
    }

    /**
     * Complete installation
     */
    public function install(Request $request)
    {
        if ($this->isInstalled()) return redirect()->route('install.complete');
        $adminData = session('install_admin');
        if (!$adminData || empty($adminData['email']) || empty($adminData['password'])) {
            return redirect()->route('install.application')->withErrors(['installation' => 'Complete the administrator setup before installing.']);
        }

        try {
            // Run migrations
            if (Artisan::call('migrate', ['--force' => true]) !== 0) {
                throw new Exception(Artisan::output() ?: 'Database migrations failed.');
            }

            // Create storage link when cPanel has not already created one.
            $publicStorage = public_path('storage');
            if (!is_link($publicStorage) && !File::exists($publicStorage)) {
                try {
                    Artisan::call('storage:link');
                } catch (\Throwable $e) {
                    // Some shared hosts disable symlinks. Keep install usable;
                    // uploaded files can still be served through the app's file route.
                    error_log('Storage symlink unavailable during install: ' . $e->getMessage());
                }
                // Artisan may return a non-zero status without throwing when the
                // host disables symlinks. Fall back to a directory in that case.
                if (!is_link($publicStorage) && !File::exists($publicStorage)) {
                    File::ensureDirectoryExists($publicStorage, 0755, true);
                }
            }

            // Seed database if requested
            if ($request->has('seed_database')) {
                Artisan::call('db:seed');
            }

            // Create admin user
            $admin = User::where('email', $adminData['email'])->first();
            if ($admin && (!$admin->is_admin || !Hash::check($adminData['password'], $admin->password))) {
                throw new Exception('That email already belongs to an account. Choose another administrator email.');
            }
            if (!$admin) {
                $admin = User::create([
                    'name' => $adminData['name'],
                    'email' => $adminData['email'],
                    'password' => Hash::make($adminData['password']),
                    'email_verified_at' => now(),
                    'is_admin' => true,
                ]);
            }

            // Clear stale cached settings before checking the production values
            // written by the installer.
            Artisan::call('config:clear');
            $checks = $this->installationChecks();
            $failedChecks = array_filter($checks, static fn (array $check) => ($check['required'] ?? true) && !$check['passed']);
            if ($failedChecks) {
                $failedNames = implode(', ', array_column($failedChecks, 'label'));
                throw new Exception('Installation checks did not pass: ' . $failedNames . '. Review cPanel permissions and configuration, then retry.');
            }

            // Shared hosts may disable symlinks or cache commands. The app runs
            // correctly without optimized caches, so only clear stale config/view caches.
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            File::put(storage_path('installed'), now()->toDateTimeString());

            // Clear installation session data
            session()->forget('install_admin');
            Auth::login($admin);
            $request->session()->regenerate();

            return redirect()->route('install.complete');

        } catch (\Throwable $e) {
            return back()->withErrors(['installation' => 'Installation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Installation complete page
     */
    public function complete()
    {
        if (!$this->isInstalled()) {
            return redirect()->route('install.index');
        }

        $checks = $this->installationChecks(true);
        $installationComplete = !array_filter($checks, static fn (array $check) => ($check['required'] ?? true) && !$check['passed']);
        return view('install.complete', compact('checks', 'installationComplete'));
    }

    /** Admin-only machine-readable installation status. */
    public function status()
    {
        $checks = $this->installationChecks(true);
        $complete = !array_filter($checks, static fn (array $check) => ($check['required'] ?? true) && !$check['passed']);
        return response()->json([
            'installed' => $this->isInstalled(),
            'complete' => $complete,
            'checks' => array_values($checks),
        ]);
    }

    /** Collect setup checks without exposing environment secrets. */
    private function installationChecks(bool $requireMarker = false): array
    {
        $databaseConnected = false;
        $pendingMigrations = [];
        $hasAdmin = false;
        try {
            DB::connection()->getPdo();
            $databaseConnected = true;
            $hasAdmin = User::where('is_admin', true)->exists();
            if (Schema::hasTable('migrations')) {
                $ran = DB::table('migrations')->pluck('migration')->all();
                $files = array_map(static fn ($file) => pathinfo($file->getFilename(), PATHINFO_FILENAME), File::files(database_path('migrations')));
                $pendingMigrations = array_values(array_diff($files, $ran));
            } else {
                $pendingMigrations = ['migrations table missing'];
            }
        } catch (\Throwable $e) {
            // Detailed connection errors stay in server logs, not the admin status response.
            error_log('Installation status database check failed: ' . $e->getMessage());
        }

        $paths = [storage_path('app'), storage_path('app/public'), storage_path('framework'), storage_path('logs'), base_path('bootstrap/cache'), public_path('uploads'), public_path('storage')];
        $runtimeWritable = count(array_filter($paths, static fn ($path) => is_dir($path) && is_writable($path))) === count($paths)
            && is_file(base_path('.env')) && is_readable(base_path('.env'));
        $appKey = (string) config('app.key');
        $productionConfig = $appKey !== '' && !empty(config('app.url')) && config('app.debug') === false && config('app.env') === 'production';
        $phpVersionOk = version_compare(PHP_VERSION, $this->requirements['php'], '>=');
        $missingExtensions = array_values(array_filter($this->requirements['extensions'], static fn ($extension) => !extension_loaded($extension)));
        $dependenciesOk = $phpVersionOk && empty($missingExtensions) && File::exists(base_path('vendor/autoload.php'));

        // Optional integrations are reported as ready, incomplete, or absent.
        // This verifies configuration presence only; it never returns credentials
        // and does not claim that a provider has accepted or validated the keys.
        $mailDriver = (string) config('mail.default');
        $mailReady = match ($mailDriver) {
            'smtp' => filled(config('mail.mailers.smtp.host')) && filled(config('mail.mailers.smtp.port'))
                && filled(config('mail.mailers.smtp.username')) && filled(config('mail.mailers.smtp.password')),
            'ses' => filled(config('services.ses.key')) && filled(config('services.ses.secret')),
            'mailgun' => filled(config('services.mailgun.secret')) && filled(config('services.mailgun.domain')),
            'sendmail' => filled(config('mail.mailers.sendmail.path')),
            'log' => false,
            default => false,
        };
        $mailConfigured = $mailReady && $mailDriver !== 'log';
        $optionalCheck = static function (string $label, bool $any, bool $all, string $readyDetail, string $missingDetail): array {
            if ($all && $any) return ['label' => $label, 'passed' => true, 'required' => false, 'state' => 'ready', 'detail' => $readyDetail];
            if (!$any) return ['label' => $label, 'passed' => true, 'required' => false, 'state' => 'optional', 'detail' => $missingDetail];
            return ['label' => $label, 'passed' => false, 'required' => false, 'state' => 'incomplete', 'detail' => 'Some settings are present, but the credential set is incomplete. Finish configuration before enabling this integration.'];
        };
        $gatewayConfigs = [
            'Paystack credentials' => ['services.paystack.public_key', 'services.paystack.secret_key'],
            'Flutterwave credentials' => ['services.flutterwave.public_key', 'services.flutterwave.secret_key'],
            'Korapay credentials' => ['services.korapay.public_key', 'services.korapay.secret_key'],
            'OPay credentials' => ['services.opay.merchant_id', 'services.opay.public_key', 'services.opay.private_key'],
        ];

        $checks = [
            'server_requirements' => ['label' => 'Server requirements and Composer dependencies', 'passed' => $dependenciesOk, 'detail' => $dependenciesOk ? 'PHP, required extensions, and Composer autoload are available.' : 'Use PHP ' . $this->requirements['php'] . '+ and install missing PHP extensions and Composer dependencies.'],
            'application_config' => ['label' => 'Production application configuration', 'passed' => $productionConfig, 'detail' => $productionConfig ? 'Application key, URL, and debug settings are configured.' : 'Set APP_KEY and APP_URL, use APP_ENV=production, and keep APP_DEBUG=false.'],
            'database' => ['label' => 'Database connection', 'passed' => $databaseConnected, 'detail' => $databaseConnected ? 'The configured database is reachable.' : 'Database connection failed. Check the cPanel database settings.'],
            'migrations' => ['label' => 'Database migrations', 'passed' => $databaseConnected && empty($pendingMigrations), 'detail' => empty($pendingMigrations) ? 'All migration files have been applied.' : count($pendingMigrations) . ' migration(s) are still pending.'],
            'administrator' => ['label' => 'Administrator account', 'passed' => $databaseConnected && $hasAdmin, 'detail' => $hasAdmin ? 'An administrator account is available.' : 'No administrator account was found.'],
            'runtime_writable' => ['label' => 'Runtime and environment permissions', 'passed' => $runtimeWritable, 'detail' => $runtimeWritable ? 'Runtime folders are writable and environment settings are readable.' : 'Make storage/, bootstrap/cache/, and public upload/storage paths writable by PHP; keep .env readable by PHP.'],
            'mail_configuration' => ['label' => 'Mail configuration (optional)', 'passed' => true, 'required' => false, 'state' => $mailConfigured ? 'ready' : 'optional', 'detail' => $mailConfigured ? 'Mail transport settings are present. Send a test email from admin settings before relying on delivery.' : ($mailDriver === 'log' ? 'Mail is set to log mode; outgoing messages will not be delivered.' : 'Mail delivery is not fully configured. You can set it up later in admin settings.')],
        ];

        foreach ($gatewayConfigs as $label => $keys) {
            $present = array_map(static fn (string $key): bool => filled(config($key)), $keys);
            $any = in_array(true, $present, true);
            $all = !in_array(false, $present, true);
            $checks[strtolower(str_replace(' ', '_', $label))] = $optionalCheck(
                $label . ' (optional)', $any, $all,
                'Credential fields are present. Confirm keys against the provider and run a server-side test before switching to production transactions.',
                'Not configured. This gateway can be added later in Admin → Environment Settings.'
            );
        }

        if ($requireMarker) {
            $checks['installation_marker'] = ['label' => 'Installation lock', 'passed' => $this->isInstalled(), 'detail' => $this->isInstalled() ? 'The installer is locked after setup.' : 'The installation marker is missing.'];
        }
        return $checks;
    }

    /**
     * Check if application is already installed
     */
    private function isInstalled()
    {
        return File::exists(storage_path('installed'));
    }

    /**
     * Check PHP version
     */
    private function checkPhpVersion()
    {
        $currentVersion = PHP_VERSION;
        $requiredVersion = $this->requirements['php'];
        
        return [
            'status' => version_compare($currentVersion, $requiredVersion, '>='),
            'current' => $currentVersion,
            'required' => $requiredVersion
        ];
    }

    /**
     * Check required PHP extensions
     */
    private function checkExtensions()
    {
        $results = [];
        $allPassed = true;

        foreach ($this->requirements['extensions'] as $extension) {
            $loaded = extension_loaded($extension);
            $results[$extension] = $loaded;
            if (!$loaded) {
                $allPassed = false;
            }
        }

        return [
            'extensions' => $results,
            'all_passed' => $allPassed
        ];
    }

    /**
     * Check directory permissions
     */
    private function checkPermissions()
    {
        $results = [];
        $allPassed = true;

        foreach ($this->requirements['permissions'] as $path => $permission) {
            $fullPath = base_path($path);
            
            if ($path === '.env') {
                $isWritable = File::exists($fullPath) ? is_writable($fullPath) : is_writable(dirname($fullPath));
                $results[$path] = [
                    'status' => $isWritable,
                    'current' => File::exists($fullPath) ? substr(sprintf('%o', fileperms($fullPath)), -4) : 'will be created',
                    'required' => 'writable by PHP',
                ];
                if (!$isWritable) $allPassed = false;
                continue;
            }

            if ($path === 'public/storage') {
                $isWritable = File::exists($fullPath) ? is_writable($fullPath) : is_writable(dirname($fullPath));
                $results[$path] = [
                    'status' => $isWritable,
                    'current' => File::exists($fullPath) ? substr(sprintf('%o', fileperms($fullPath)), -4) : 'will be linked',
                    'required' => 'writable by PHP',
                ];
                if (!$isWritable) $allPassed = false;
                continue;
            }

            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }

            $isWritable = is_writable($fullPath);
            $results[$path] = [
                'status' => $isWritable,
                'current' => substr(sprintf('%o', fileperms($fullPath)), -4),
                'required' => $permission
            ];

            if (!$isWritable) {
                $allPassed = false;
            }
        }

        return [
            'permissions' => $results,
            'all_passed' => $allPassed
        ];
    }

    /**
     * Check if Composer is available
     */
    private function checkComposer()
    {
        $composerPath = base_path('vendor/autoload.php');
        return [
            'status' => File::exists($composerPath),
            'path' => $composerPath
        ];
    }

    /**
     * Check if Node.js is available (optional)
     */
    private function checkNode()
    {
        if (!function_exists('shell_exec')) {
            return ['status' => false, 'version' => 'Not installed (optional)'];
        }
        $output = @shell_exec('node --version 2>&1');
        return [
            'status' => is_string($output) && str_starts_with($output, 'v'),
            'version' => is_string($output) && trim($output) !== '' ? trim($output) : 'Not installed (optional)'
        ];
    }

    /**
     * Test database connection (private helper)
     */
    private function testDbConnection($host, $port, $database, $username, $password)
    {
        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port};dbname={$database}",
                $username,
                $password,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            return ['success' => true, 'message' => 'Connection successful'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Test database connection via AJAX
     */
    public function testDatabaseConnection(Request $request)
    {
        if ($this->isInstalled()) abort(404);
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_name' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        $result = $this->testDbConnection(
            $request->db_host,
            $request->db_port,
            $request->db_name,
            $request->db_username,
            $request->db_password
        );

        return response()->json($result);
    }

    /**
     * Update .env file
     */
    private function updateEnvFile($data)
    {
        $envFile = base_path('.env');
        $envContent = File::exists($envFile) ? File::get($envFile) : '';

        foreach ($data as $key => $value) {
            $value = $value === null ? '' : (string)$value;
            // Quote values so spaces, #, and special characters in cPanel
            // credentials are parsed correctly by Dotenv.
            if ($value !== '' && !preg_match('/^(true|false|null|[0-9]+|base64:.*)$/i', $value)) {
                $value = '"' . str_replace(["\\", '"', "\r", "\n"], ["\\\\", '\\"', '', ''], $value) . '"';
            }
            
            $pattern = '/^' . preg_quote($key, '/') . '=.*/m';
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace_callback($pattern, static fn() => $key . '=' . $value, $envContent, 1);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envFile, $envContent);
    }


}
