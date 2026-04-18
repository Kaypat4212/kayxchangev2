<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Exception;

class InstallController extends Controller
{
    private $requirements = [
        'php' => '8.1.0',
        'extensions' => [
            'OpenSSL',
            'PDO',
            'Mbstring',
            'Tokenizer',
            'XML',
            'Ctype',
            'JSON',
            'BCMath',
            'Curl',
            'GD',
            'Zip'
        ],
        'permissions' => [
            'storage/app' => '775',
            'storage/framework' => '775',
            'storage/logs' => '775',
            'bootstrap/cache' => '775',
            'public/uploads' => '775',
            'public/storage' => '775'
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
                     $checks['permissions']['all_passed'];

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
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
            'telegram_bot_token' => 'nullable|string',
            'mail_driver' => 'required|in:smtp,sendmail,mailgun,ses,log',
            'mail_host' => 'required_if:mail_driver,smtp|string',
            'mail_port' => 'required_if:mail_driver,smtp|numeric',
            'mail_username' => 'required_if:mail_driver,smtp|string',
            'mail_password' => 'required_if:mail_driver,smtp|string',
            'mail_encryption' => 'required_if:mail_driver,smtp|in:tls,ssl,null',
            'paystack_public_key' => 'nullable|string',
            'paystack_secret_key' => 'nullable|string',
        ]);

        try {
            // Update .env file with application settings
            $envData = [
                'APP_NAME' => '"' . $request->app_name . '"',
                'APP_URL' => $request->app_url,
                'APP_KEY' => 'base64:' . base64_encode(random_bytes(32)),
                'MAIL_MAILER' => $request->mail_driver,
                'MAIL_HOST' => $request->mail_host,
                'MAIL_PORT' => $request->mail_port,
                'MAIL_USERNAME' => $request->mail_username,
                'MAIL_PASSWORD' => $request->mail_password,
                'MAIL_ENCRYPTION' => $request->mail_encryption,
                'MAIL_FROM_ADDRESS' => $request->admin_email,
                'MAIL_FROM_NAME' => '"' . $request->app_name . '"',
            ];

            if ($request->telegram_bot_token) {
                $envData['TELEGRAM_BOT_TOKEN'] = $request->telegram_bot_token;
            }

            if ($request->paystack_public_key && $request->paystack_secret_key) {
                $envData['PAYSTACK_PUBLIC_KEY'] = $request->paystack_public_key;
                $envData['PAYSTACK_SECRET_KEY'] = $request->paystack_secret_key;
            }

            $this->updateEnvFile($envData);

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

        return view('install.final');
    }

    /**
     * Complete installation
     */
    public function install(Request $request)
    {
        try {
            // Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // Create storage link
            Artisan::call('storage:link');

            // Seed database if requested
            if ($request->has('seed_database')) {
                Artisan::call('db:seed');
            }

            // Create admin user
            $adminData = session('install_admin');
            if ($adminData) {
                User::create([
                    'name' => $adminData['name'],
                    'email' => $adminData['email'],
                    'password' => Hash::make($adminData['password']),
                    'email_verified_at' => now(),
                    'is_admin' => true,
                ]);
            }

            // Create installation marker
            File::put(storage_path('installed'), now()->toDateTimeString());

            // Clear caches
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            // Clear installation session data
            session()->forget('install_admin');

            return redirect()->route('install.complete');

        } catch (Exception $e) {
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

        return view('install.complete');
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
        $output = shell_exec('node --version 2>&1');
        return [
            'status' => strpos($output, 'v') === 0,
            'version' => $output ? trim($output) : 'Not found'
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
            $value = $value === null ? '' : $value;
            
            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envFile, $envContent);
    }


}
