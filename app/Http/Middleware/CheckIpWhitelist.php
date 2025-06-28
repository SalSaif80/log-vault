<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        // الحصول على عنوان IP للعميل
        $clientIp = $request->ip();

        // إضافة logging للمساعدة في debugging
        Log::info('IP Whitelist Check', [
            'client_ip' => $clientIp,
            'whitelist' => $whitelist,
            'user_agent' => $request->userAgent(),
            'request_path' => $request->path()
        ]);

        // التحقق من عنوان IP
        if (!$this->isIpAllowed($clientIp, $whitelist)) {
            Log::warning('IP Access Denied', [
                'client_ip' => $clientIp,
                'whitelist' => $whitelist
            ]);

            // إذا كان request من API، أرجع JSON response مع معلومات debugging
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'عذراً، عنوان IP الخاص بك غير مسموح.',
                    'client_ip' => $clientIp, // إضافة IP للـ debugging
                ], 403);
            }

            // إذا كان request من web، أرجع 403 page
            abort(403, 'Unauthorized IP address: ' . $clientIp);
        }

        return $next($request);
    }

    /**
     * تحقق من أن الـ IP مسموح
     */
    private function isIpAllowed(string $clientIp, array $whitelist): bool
    {
        // تحقق مباشر
        if (in_array($clientIp, $whitelist)) {
            return true;
        }

        // تحقق من IPv6 shortened forms
        foreach ($whitelist as $allowedIp) {
            if ($this->compareIpAddresses($clientIp, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * مقارنة عناوين IP مع دعم IPv6
     */
    private function compareIpAddresses(string $ip1, string $ip2): bool
    {
        // إذا كانا متطابقين تماماً
        if ($ip1 === $ip2) {
            return true;
        }

        // تحويل إلى binary للمقارنة الدقيقة
        $binary1 = @inet_pton($ip1);
        $binary2 = @inet_pton($ip2);

        if ($binary1 === false || $binary2 === false) {
            return false;
        }

        return $binary1 === $binary2;
    }
}
