<?php

namespace App\Http\Middleware;

use App\Models\AdminSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureToolsPageEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless((string) (AdminSetting::getSetting('tools_page_enabled') ?? '1') === '1', 404);

        return $next($request);
    }
}
