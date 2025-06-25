<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    /**
     * عرض قائمة السجلات مع الفلترة
     */
    public function index(Request $request)
    {
        $query = Log::orderBy('occurred_at', 'desc');

        // فلاتر بسيطة
        if ($request->filled('source_system')) {
            $query->where('source_system', $request->source_system);
        }

        if ($request->filled('event')) {
            $query->where('event', 'like', '%' . $request->event . '%');
        }

        if ($request->filled('start_date')) {
            $query->where('occurred_at', '>=', Carbon::parse($request->start_date));
        }

        if ($request->filled('end_date')) {
            $query->where('occurred_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        if ($request->filled('batch_uuid')) {
            $query->where('batch_uuid', $request->batch_uuid);
        }

        // ترتيب وتصفح
        $perPage = min($request->input('per_page', 50), 100);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ]
        ]);
    }

    /**
     * استقبال دفعة من السجلات من mini-school
     */
    public function storeBatch(Request $request)
    {
        try {
            // التحقق من وجود البيانات
            $validator = Validator::make($request->all(), [
                'logs' => 'required|array|min:1',
                'logs.*.external_log_id' => 'required|integer',
                'logs.*.description' => 'required|string',
                'logs.*.causer_type' => 'nullable|string',
                'logs.*.causer_id' => 'nullable|integer',
                'logs.*.subject_type' => 'nullable|string',
                'logs.*.subject_id' => 'nullable|integer',
                'logs.*.project_name' => 'required|string',
                'logs.*.occurred_at' => 'required|string',
                'logs.*.properties' => 'nullable|array',
                'logs.*.event' => 'nullable|string',
                'logs.*.log_name' => 'nullable|string',
                'logs.*.source_system' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $logsData = $request->input('logs');
            $savedLogs = [];

            DB::beginTransaction();

            foreach ($logsData as $logData) {
                // تحويل التاريخ من ISO string إلى Carbon
                $occurredAt = Carbon::parse($logData['occurred_at']);

                // التحقق من عدم وجود السجل مسبقاً
                $existingLog = Log::where('external_log_id', $logData['external_log_id'])
                                ->where('source_system', $logData['source_system'])
                                ->first();

                if ($existingLog) {
                    continue; // تجاهل إذا موجود
                }

                // إنشاء السجل المبسط
                $log = Log::create([
                    'external_log_id' => $logData['external_log_id'],
                    'description' => $logData['description'],
                    'causer_type' => $logData['causer_type'] ?? null,
                    'causer_id' => $logData['causer_id'] ?? null,
                    'subject_type' => $logData['subject_type'] ?? null,
                    'subject_id' => $logData['subject_id'] ?? null,
                    'project_name' => $logData['project_name'],
                    'occurred_at' => $occurredAt,
                    'properties' => $logData['properties'] ?? null,
                    'event' => $logData['event'] ?? null,
                    'log_name' => $logData['log_name'] ?? null,
                    'source_system' => $logData['source_system'],
                ]);

                $savedLogs[] = $log->id;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ السجلات بنجاح',
                'saved_count' => count($savedLogs),
                'saved_ids' => $savedLogs
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ السجلات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء سجل واحد (للاختبار السريع)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'log_id' => 'nullable|integer',
                'log_name' => 'nullable|string',
                'description' => 'required|string',
                'subject_type' => 'nullable|string',
                'subject_id' => 'nullable|integer',
                'event' => 'nullable|string',
                'causer_type' => 'nullable|string',
                'causer_id' => 'nullable|integer',
                'batch_uuid' => 'nullable|string',
                'properties' => 'nullable|array',
                'source_system' => 'required|string',
                'occurred_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $log = Log::create([
                'external_log_id' => $request->input('log_id'),
                'log_name' => $request->input('log_name'),
                'description' => $request->input('description'),
                'subject_type' => $request->input('subject_type'),
                'subject_id' => $request->input('subject_id'),
                'event' => $request->input('event'),
                'causer_type' => $request->input('causer_type'),
                'causer_id' => $request->input('causer_id'),
                'batch_uuid' => $request->input('batch_uuid'),
                'properties' => $request->input('properties'),
                'source_system' => $request->input('source_system'),
                'occurred_at' => $request->input('occurred_at') ? Carbon::parse($request->input('occurred_at')) : now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء السجل بنجاح',
                'data' => $log
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء السجل',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض سجل محدد
     */
    public function show($id)
    {
        $log = Log::find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Log not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $log
        ]);
    }

    /**
     * إحصائيات بسيطة
     */
    public function statistics(Request $request)
    {
        $query = Log::query();

        // فلتر بالتاريخ إذا وجد
        if ($request->filled('start_date')) {
            $query->where('occurred_at', '>=', Carbon::parse($request->start_date));
        }
        if ($request->filled('end_date')) {
            $query->where('occurred_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        if ($request->filled('source_system')) {
            $query->where('source_system', $request->source_system);
        }

        $statistics = [
            'total_logs' => $query->count(),
            'source_systems' => Log::groupBy('source_system')
                ->selectRaw('source_system, count(*) as count')
                ->get()
                ->pluck('count', 'source_system'),
            'events_count' => $query->groupBy('event')
                ->selectRaw('event, count(*) as count')
                ->get()
                ->pluck('count', 'event'),
            'daily_counts' => $query->selectRaw('DATE(occurred_at) as date, count(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get(),
            'latest_batch' => Log::orderBy('created_at', 'desc')->first()?->batch_uuid,
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * تصدير بسيط
     */
    public function export(Request $request)
    {
        $query = Log::query();

        // تطبيق فلاتر
        if ($request->filled('source_system')) {
            $query->where('source_system', $request->source_system);
        }

        if ($request->filled('start_date')) {
            $query->where('occurred_at', '>=', Carbon::parse($request->start_date));
        }

        if ($request->filled('end_date')) {
            $query->where('occurred_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $logs = $query->orderBy('occurred_at', 'desc')->limit(1000)->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
            'count' => $logs->count()
        ]);
    }

    /**
     * إحصائيات متقدمة للإدارة
     */
    public function analytics(Request $request)
    {
        $analytics = [
            'total_logs' => Log::count(),
            'today_logs' => Log::whereDate('occurred_at', today())->count(),
            'week_logs' => Log::where('occurred_at', '>=', now()->subWeek())->count(),
            'month_logs' => Log::where('occurred_at', '>=', now()->subMonth())->count(),
            'top_events' => Log::groupBy('event')
                ->selectRaw('event, count(*) as count')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'recent_logs' => Log::orderBy('occurred_at', 'desc')
                ->limit(20)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * فحص صحة الAPI
     */
    public function health()
    {
        return response()->json([
            'success' => true,
            'message' => 'LogVault API is working',
            'timestamp' => now()->toISOString(),
            'total_logs' => Log::count(),
            'last_log' => Log::latest('created_at')->first()?->created_at?->toISOString()
        ]);
    }
}
