<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\Log\StoreBatchRequest;
use Illuminate\Support\Facades\Log as FacadesLog;

class LogController extends Controller
{

    /**
     * استقبال دفعة من السجلات من mini-school
     */
    public function storeBatch(StoreBatchRequest $request)
    {
        try {
            // الحصول على معلومات المشروع من الـ middleware
            $project = $request->validated_project;
            $logsData = $request->validated('logs');
            $savedLogs = [];

            DB::beginTransaction();

            foreach ($logsData as $logData) {
                try {
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
                        'project_id' => $project->id,
                        'occurred_at' => Carbon::parse($logData['occurred_at']),
                        'properties' => $logData['properties'] ?? null,
                        'event' => $logData['event'] ?? null,
                        'log_name' => $logData['log_name'] ?? null,
                        'source_system' => $project->slug,
                    ]);

                    $savedLogs[] = $log->id;

                } catch (\Exception $e) {
                    // تسجيل الخطأ للمراجعة لاحقاً
                    FacadesLog::error('خطأ في معالجة سجل واحد', [
                        'log_data' => $logData,
                        'project_id' => $project->id,
                        'project_slug' => $project->slug,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    // الاستمرار مع السجل التالي بدلاً من إيقاف العملية بالكامل
                    continue;
                }
            }

            DB::commit();

            // تسجيل العملية الناجحة
            FacadesLog::info('تم حفظ دفعة سجلات بنجاح', [
                'project_id' => $project->id,
                'project_slug' => $project->slug,
                'saved_count' => count($savedLogs),
                'total_received' => count($logsData),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ السجلات بنجاح',
                'saved_count' => count($savedLogs),
                'total_received' => count($logsData),
                'project' => $project->name,
                'saved_ids' => $savedLogs
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            FacadesLog::error('خطأ عام في حفظ دفعة السجلات', [
                'project_id' => $request->validated_project->id ?? null,
                'project_slug' => $request->validated_project->slug ?? null,
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ السجلات',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
