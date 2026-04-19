<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Resolve Application Base Path
|--------------------------------------------------------------------------
|
| Shared hosting deployments often place this public directory under
| public_html while the Laravel app lives in a sibling folder. We try a
| small set of safe candidate paths and pick the first valid installation.
|
*/

$basePathCandidates = [];
$knownPaths = [];

$addBasePathCandidate = static function ($path) use (&$basePathCandidates, &$knownPaths): void {
    if (!$path) {
        return;
    }

    $realPath = realpath($path);
    if (!$realPath || isset($knownPaths[$realPath])) {
        return;
    }

    $knownPaths[$realPath] = true;
    $basePathCandidates[] = $realPath;
};

$addBasePathCandidate(__DIR__.'/..');
$addBasePathCandidate(__DIR__.'/../kayxchangev2');
$addBasePathCandidate(__DIR__.'/../../kayxchangev2');
$addBasePathCandidate(__DIR__.'/../laravel');
$addBasePathCandidate(__DIR__.'/../../laravel');

$envBasePath = getenv('LARAVEL_BASE_PATH');
if (is_string($envBasePath) && $envBasePath !== '') {
    $addBasePathCandidate($envBasePath);
}

$scanRoots = [];
$current = __DIR__;
for ($i = 0; $i < 5; $i++) {
    $root = realpath($current);
    if (!$root) {
        break;
    }

    $scanRoots[] = $root;
    $parent = dirname($current);
    if ($parent === $current) {
        break;
    }

    $current = $parent;
}

$commonFolders = ['app', 'laravel', 'kayxchange', 'kayxchangev2', 'project'];
foreach ($scanRoots as $root) {
    $addBasePathCandidate($root);

    foreach ($commonFolders as $folder) {
        $addBasePathCandidate($root.'/'.$folder);
    }

    $entries = @scandir($root);
    if (!is_array($entries)) {
        continue;
    }

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $fullPath = $root.'/'.$entry;
        if (is_dir($fullPath)) {
            $addBasePathCandidate($fullPath);
        }
    }
}

$resolvedBasePath = null;
foreach ($basePathCandidates as $candidate) {
    if (!$candidate) {
        continue;
    }

    if (file_exists($candidate.'/vendor/autoload.php') && file_exists($candidate.'/bootstrap/app.php')) {
        $resolvedBasePath = $candidate;
        break;
    }
}

if ($resolvedBasePath === null) {
    http_response_code(500);
    exit('Laravel bootstrap files were not found. Ensure vendor and bootstrap exist, or set LARAVEL_BASE_PATH.');
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require $resolvedBasePath.'/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once $resolvedBasePath.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
