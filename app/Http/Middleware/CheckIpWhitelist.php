<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // قائمة عناوين IP المسموحة
        $whitelist = array_filter(explode(',', env('IP_WHITELIST', '192.168.10.59')));

                // التحقق من عنوان IP
        $clientIp = $request->ip();

        if (!in_array($clientIp, $whitelist)) {
            // إذا كان request من API، أرجع JSON response مع معلومات debugging
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'عذراً، عنوان IP الخاص بك غير مسموح.',
                    'clientIp' => $clientIp,
                ], 403);
            }

            // إذا كان request من web، أرجع 403 page
            abort(403, 'Unauthorized IP address: ' . $clientIp);
        }

        return $next($request);
    }
}
