<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\Log\StoreBatchRequest;

class LogController extends Controller
{


    /**
     * استقبال دفعة من السجلات من mini-school
     */
    public function storeBatch(StoreBatchRequest $request)
    {
        try {


            $logsData = $request->validated('logs');
            $savedLogs = [];

            DB::beginTransaction();

            foreach ($logsData as $logData) {

                // التحقق من عدم وجود السجل مسبقاً
                $existingLog = Log::where('external_log_id', $logData['external_log_id'])
                                ->where('source_system', $logData['source_system'])
                                ->first();

                if ($existingLog) {
                    continue; // تجاهل إذا موجود
                }

                $token = $request->user()->currentAccessToken();
                $projectSlug = $token->abilities[0] ?? null;
                $project = Project::where('slug', $projectSlug)->first();

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

}
