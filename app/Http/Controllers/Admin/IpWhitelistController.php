<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpWhitelist;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class IpWhitelistController extends Controller
{
    /**
     * عرض قائمة IP whitelist للمشروع
     */
    public function index(Project $project): View
    {
        $ips = $project->ipWhitelist()
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('admin.ip-whitelist.index', compact('project', 'ips'));
    }

    /**
     * عرض نموذج إضافة IP جديد
     */
    public function create(Project $project): View
    {
        return view('admin.ip-whitelist.create', compact('project'));
    }

    /**
     * حفظ IP جديد
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'ip_address' => [
                'required',
                'string',
                'max:45',
                function ($attribute, $value, $fail) use ($project) {
                    // التحقق من عدم وجود IP مكرر
                    if ($project->ipWhitelist()->where('ip_address', $value)->exists()) {
                        $fail('عنوان IP موجود مسبقاً في هذا المشروع.');
                    }

                    // التحقق من صحة تنسيق IP
                    if (!$this->validateIpFormat($value)) {
                        $fail('تنسيق عنوان IP غير صحيح.');
                    }
                },
            ],
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $project->ipWhitelist()->create($validated);

        return redirect()
            ->route('admin.projects.ip-whitelist.index', $project)
            ->with('success', 'تم إضافة عنوان IP بنجاح.');
    }

    /**
     * عرض تفاصيل IP
     */
    public function show(Project $project, IpWhitelist $ipWhitelist): View
    {
        return view('admin.ip-whitelist.show', compact('project', 'ipWhitelist'));
    }

    /**
     * عرض نموذج تعديل IP
     */
    public function edit(Project $project, IpWhitelist $ipWhitelist): View
    {
        return view('admin.ip-whitelist.edit', compact('project', 'ipWhitelist'));
    }

    /**
     * تحديث IP
     */
    public function update(Request $request, Project $project, IpWhitelist $ipWhitelist): RedirectResponse
    {
        $validated = $request->validate([
            'ip_address' => [
                'required',
                'string',
                'max:45',
                function ($attribute, $value, $fail) use ($project, $ipWhitelist) {
                    // التحقق من عدم وجود IP مكرر (ما عدا الحالي)
                    if ($project->ipWhitelist()
                        ->where('ip_address', $value)
                        ->where('id', '!=', $ipWhitelist->id)
                        ->exists()) {
                        $fail('عنوان IP موجود مسبقاً في هذا المشروع.');
                    }

                    // التحقق من صحة تنسيق IP
                    if (!$this->validateIpFormat($value)) {
                        $fail('تنسيق عنوان IP غير صحيح.');
                    }
                },
            ],
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $ipWhitelist->update($validated);

        return redirect()
            ->route('admin.projects.ip-whitelist.index', $project)
            ->with('success', 'تم تحديث عنوان IP بنجاح.');
    }

    /**
     * حذف IP
     */
    public function destroy(Project $project, IpWhitelist $ipWhitelist): RedirectResponse
    {
        $ipWhitelist->delete();

        return redirect()
            ->route('admin.projects.ip-whitelist.index', $project)
            ->with('success', 'تم حذف عنوان IP بنجاح.');
    }

    /**
     * تغيير حالة IP (AJAX)
     */
    public function toggleStatus(IpWhitelist $ipWhitelist): JsonResponse
    {
        try {
            $ipWhitelist->toggleStatus();

            return response()->json([
                'success' => true,
                'status' => $ipWhitelist->status,
                'message' => 'تم تغيير حالة عنوان IP بنجاح.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تغيير حالة عنوان IP.'
            ], 500);
        }
    }

    /**
     * اختبار IP (AJAX)
     */
    public function testIp(Request $request): JsonResponse
    {
        $request->validate([
            'ip' => 'required|string',
            'project_id' => 'required|exists:projects,id',
        ]);

        $isAllowed = IpWhitelist::isIpAllowed($request->ip, $request->project_id);

        return response()->json([
            'allowed' => $isAllowed,
            'ip' => $request->ip,
            'message' => $isAllowed ? 'IP مسموح' : 'IP غير مسموح'
        ]);
    }

    /**
     * عرض إحصائيات IP whitelist
     */
    public function statistics(Project $project): JsonResponse
    {
        $stats = [
            'total' => $project->ipWhitelist()->count(),
            'active' => $project->ipWhitelist()->active()->count(),
            'inactive' => $project->ipWhitelist()->where('status', 'inactive')->count(),
            'by_type' => [
                'direct' => $project->ipWhitelist()->where('ip_address', 'not like', '%*%')
                    ->where('ip_address', 'not like', '%/%')->count(),
                'wildcard' => $project->ipWhitelist()->where('ip_address', 'like', '%*%')->count(),
                'cidr' => $project->ipWhitelist()->where('ip_address', 'like', '%/%')->count(),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * التحقق من صحة تنسيق IP
     */
    private function validateIpFormat(string $ip): bool
    {
        // IP مباشر (IPv4 أو IPv6)
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }

        // CIDR notation
        if (strpos($ip, '/') !== false) {
            [$subnet, $mask] = explode('/', $ip);

            // التحقق من صحة الشبكة
            if (!filter_var($subnet, FILTER_VALIDATE_IP)) {
                return false;
            }

            // التحقق من صحة القناع
            $maskInt = intval($mask);
            if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $maskInt >= 0 && $maskInt <= 32;
            } elseif (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return $maskInt >= 0 && $maskInt <= 128;
            }
        }

        // Wildcard pattern
        if (strpos($ip, '*') !== false) {
            // التحقق من أن النمط يحتوي على أرقام وعلامات * و . فقط
            return preg_match('/^[0-9.*]+$/', $ip) &&
                   substr_count($ip, '.') === 3;
        }

        return false;
    }

    /**
     * استيراد عناوين IP من ملف
     */
    public function import(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,csv|max:2048',
        ]);

        try {
            $content = file_get_contents($request->file('file')->getRealPath());
            $lines = array_filter(array_map('trim', explode("\n", $content)));

            $imported = 0;
            $errors = [];

            foreach ($lines as $index => $line) {
                // تخطي التعليقات والأسطر الفارغة
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }

                // فصل IP والوصف (إذا كان موجود)
                $parts = explode(',', $line, 2);
                $ip = trim($parts[0]);
                $description = isset($parts[1]) ? trim($parts[1]) : null;

                // التحقق من تنسيق IP
                if (!$this->validateIpFormat($ip)) {
                    $errors[] = "السطر " . ($index + 1) . ": تنسيق IP غير صحيح - $ip";
                    continue;
                }

                // التحقق من عدم التكرار
                if ($project->ipWhitelist()->where('ip_address', $ip)->exists()) {
                    $errors[] = "السطر " . ($index + 1) . ": IP موجود مسبقاً - $ip";
                    continue;
                }

                // إضافة IP
                $project->ipWhitelist()->create([
                    'ip_address' => $ip,
                    'description' => $description,
                    'status' => 'active',
                ]);

                $imported++;
            }

            $message = "تم استيراد $imported عنوان IP بنجاح.";
            if (!empty($errors)) {
                $message .= " مع " . count($errors) . " أخطاء.";
            }

            return redirect()
                ->route('admin.projects.ip-whitelist.index', $project)
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'فشل في استيراد الملف: ' . $e->getMessage());
        }
    }

    /**
     * تصدير عناوين IP
     */
    public function export(Project $project): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'ip-whitelist-' . $project->slug . '-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($project) {
            $handle = fopen('php://output', 'w');

            // كتابة الترويسة
            fputcsv($handle, [
                'IP Address',
                'Description',
                'Status',
                'Type',
                'Created At'
            ]);

            // كتابة البيانات
            $project->ipWhitelist()->chunk(1000, function ($ips) use ($handle) {
                foreach ($ips as $ip) {
                    fputcsv($handle, [
                        $ip->ip_address,
                        $ip->description,
                        $ip->status,
                        $ip->pattern_type,
                        $ip->created_at->format('Y-m-d H:i:s')
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
