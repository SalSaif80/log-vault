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
     * العلاقة مع السجلات
     */
    public function logs()
    {
        return $this->hasMany(\App\Models\Log::class);
    }

    /**
     * الحصول على إحصائيات السجلات المرتبطة بهذا المشروع
     */
    public function getLogsCountAttribute()
    {
        return $this->logs()->count();
    }

    /**
     * الحصول على آخر سجل لهذا المشروع
     */
    public function getLatestLogAttribute()
    {
        return $this->logs()->latest('occurred_at')->first();
    }

    /**
     * إحصائيات بسيطة للمشروع
     */
    public function getStatsAttribute()
    {
        return [
            'total_logs' => $this->logs()->count(),
            'today_logs' => $this->logs()->whereDate('occurred_at', today())->count(),
            'week_logs' => $this->logs()->where('occurred_at', '>=', now()->subWeek())->count(),
            'latest_log' => $this->logs()->latest('occurred_at')->first(),
        ];
    }
}
