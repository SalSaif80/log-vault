<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Project;
use Illuminate\Support\Facades\Log as FacadesLog;
use Laravel\Sanctum\PersonalAccessToken;

class ValidateProjectToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // إجبار API routes على قبول JSON فقط (هذا يحل مشكلة HTML response)
        $request->headers->set('Accept', 'application/json');

        try {
            // التحقق من وجود توكن في الطلب
            $bearerToken = $request->bearerToken();
            if (!$bearerToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'توكن الوصول مطلوب - يجب إرسال Authorization Bearer token',
                    'error_code' => 'MISSING_TOKEN'
                ], 401);
            }

            // البحث عن التوكن في قاعدة البيانات
            $accessToken = PersonalAccessToken::findToken($bearerToken);
            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'توكن غير صالح أو منتهي الصلاحية',
                    'error_code' => 'INVALID_TOKEN'
                ], 401);
            }

            // التحقق من انتهاء صلاحية التوكن
            if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'توكن منتهي الصلاحية',
                    'error_code' => 'TOKEN_EXPIRED'
                ], 401);
            }

            // الحصول على المستخدم المرتبط بالتوكن
            $user = $accessToken->tokenable;
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم المرتبط بالتوكن غير موجود',
                    'error_code' => 'USER_NOT_FOUND'
                ], 401);
            }

            // تعيين المستخدم للطلب (محاكاة سلوك auth:sanctum)
            $request->setUserResolver(function () use ($user, $accessToken) {
                $user->withAccessToken($accessToken);
                return $user;
            });

            // التحقق من وجود صلاحيات في التوكن
            if (!$accessToken->abilities || empty($accessToken->abilities)) {
                return response()->json([
                    'success' => false,
                    'message' => 'التوكن لا يحتوي على صلاحيات صالحة',
                    'error_code' => 'INVALID_TOKEN_ABILITIES'
                ], 403);
            }

            // الحصول على معرف المشروع من الصلاحيات
            $projectSlug = $accessToken->abilities[0] ?? null;
            if (!$projectSlug) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم العثور على معرف المشروع في التوكن',
                    'error_code' => 'PROJECT_SLUG_MISSING'
                ], 403);
            }

            // البحث عن المشروع في قاعدة البيانات
            $project = Project::where('slug', $projectSlug)->first();
            if (!$project) {
                FacadesLog::warning('محاولة وصول بتوكن يحتوي على مشروع غير موجود', [
                    'project_slug' => $projectSlug,
                    'user_id' => $user->id,
                    'token_id' => $accessToken->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'route' => $request->route()->getName() ?? $request->path()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'المشروع المرتبط بالتوكن غير موجود: ' . $projectSlug,
                    'error_code' => 'PROJECT_NOT_FOUND'
                ], 403);
            }

            // التحقق من أن المشروع نشط (إذا كان لديك حقل status)
            if (isset($project->status) && $project->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'المشروع المرتبط بالتوكن غير نشط',
                    'error_code' => 'PROJECT_INACTIVE'
                ], 403);
            }

            // تحديث وقت آخر استخدام للتوكن
            $accessToken->forceFill(['last_used_at' => now()])->save();

            // إضافة معلومات المشروع إلى الطلب للاستخدام في الكنترولر
            $request->merge(['validated_project' => $project]);

            return $next($request);

        } catch (\Exception $e) {
            FacadesLog::error('خطأ في middleware التحقق من التوكن والمشروع', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip(),
                'route' => $request->route()->getName() ?? $request->path()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحقق من صحة التوكن',
                'error_code' => 'TOKEN_VALIDATION_ERROR'
            ], 500);
        }
    }
}
