<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * إنشاء slug تلقائياً عند الحفظ
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);

                // التأكد من فرادة الـ slug
                $originalSlug = $project->slug;
                $counter = 1;
                while (static::where('slug', $project->slug)->exists()) {
                    $project->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    /**
     * التحقق من حالة النشاط
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * الحصول على إحصائيات السجلات المرتبطة بهذا المشروع عبر source_system
     * (إذا كان اسم المشروع يطابق source_system)
     */
    public function getLogsCountAttribute()
    {
        return \App\Models\Log::where('source_system', $this->slug)->count();
    }

    /**
     * الحصول على آخر سجل لهذا المشروع
     */
    public function getLatestLogAttribute()
    {
        return \App\Models\Log::where('source_system', $this->slug)
            ->latest('created_at')
            ->first();
    }

    /**
     * إحصائيات بسيطة للمشروع
     */
    public function getStatsAttribute()
    {
        $sourceSystem = $this->slug;

        return [
            'total_logs' => \App\Models\Log::where('source_system', $sourceSystem)->count(),
            'today_logs' => \App\Models\Log::where('source_system', $sourceSystem)
                ->whereDate('created_at', today())->count(),
            'week_logs' => \App\Models\Log::where('source_system', $sourceSystem)
                ->where('created_at', '>=', now()->subWeek())->count(),
            'latest_log' => \App\Models\Log::where('source_system', $sourceSystem)
                ->latest('created_at')->first(),
        ];
    }
}
