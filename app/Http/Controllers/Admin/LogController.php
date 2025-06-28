<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LogController extends Controller
{
    /**
     * عرض قائمة السجلات
     */
    public function index(Request $request)
    {
        $query = Log::with('project')->orderBy('occurred_at', 'desc');

        // تطبيق الفلاتر
        if ($request->filled('source_system')) {
            $query->where('source_system', $request->source_system);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('project_name')) {
            $query->where('project_name', $request->project_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('start_date')) {
            $query->where('occurred_at', '>=', Carbon::parse($request->start_date));
        }

        if ($request->filled('end_date')) {
            $query->where('occurred_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('causer_type', 'like', "%{$search}%")
                    ->orWhere('source_system', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%")
                    ->orWhereJsonContains('properties->attributes->name', $search)
                    ->orWhereJsonContains('properties->attributes->course_name', $search);
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // جلب المشاريع والأنظمة المصدر للفلاتر
        $projects = Project::orderBy('name')->get();
        $sourceSystems = Log::distinct()->pluck('source_system')->filter()->sort();

        // إحصائيات سريعة
        $stats = [
            'total_logs' => Log::count(),
            'today_logs' => Log::whereDate('occurred_at', today())->count(),
            'week_logs' => Log::where('occurred_at', '>=', now()->subWeek())->count(),
            'active_systems' => Log::distinct()->count('source_system'),
        ];

        return view('admin.logs.index', compact('logs', 'projects', 'sourceSystems', 'stats'));
    }

    /**
     * عرض سجل محدد
     */
    public function show(Log $log)
    {
        return view('admin.logs.show', compact('log'));
    }

    /**
     * حذف سجل
     */
    public function destroy(Log $log)
    {
        $log->delete();

        return redirect()->route('admin.logs.index')
            ->with('success', 'تم حذف السجل بنجاح');
    }



    /**
     * تصدير السجلات
     */
    public function export(Request $request)
    {
        $query = Log::query();

        // تطبيق نفس الفلاتر
        if ($request->filled('source_system')) {
            $query->where('source_system', $request->source_system);
        }

        if ($request->filled('project_name')) {
            $query->where('project_name', $request->project_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('start_date')) {
            $query->where('occurred_at', '>=', Carbon::parse($request->start_date));
        }

        if ($request->filled('end_date')) {
            $query->where('occurred_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $logs = $query->orderBy('occurred_at', 'desc')->limit(5000)->get(); // حد أقصى 5000 سجل

        $filename = 'logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // إضافة BOM لدعم UTF-8 في Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // إضافة headers
            fputcsv($file, [
                'رقم السجل الخارجي',
                'اسم السجل',
                'الوصف',
                'نوع الموضوع',
                'معرف الموضوع',
                'الحدث',
                'نوع المسبب',
                'معرف المسبب',
                'اسم المشروع',
                'معرف المجموعة',
                'النظام المصدر',
                'تاريخ الحدث',
                'تاريخ الإرسال'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->external_log_id ?? $log->id,
                    $log->log_name ?? '',
                    $log->description,
                    $log->subject_type ?? '',
                    $log->subject_id ?? '',
                    $log->event ?? '',
                    $log->causer_type ?? '',
                    $log->causer_id ?? '',
                    $log->project_name ?? '',
                    $log->batch_uuid ?? '',
                    $log->source_system,
                    $log->occurred_at ? $log->occurred_at->format('Y-m-d H:i:s') : '',
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
