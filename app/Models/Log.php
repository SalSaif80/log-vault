<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_log_id',
        'description',
        'causer_type',
        'causer_id',
        'subject_type',
        'subject_id',
        'project_name',
        'occurred_at',
        'properties',
        'event',
        'log_name',
        'source_system',
        'batch_uuid',
    ];

    protected $casts = [
        'properties' => 'array',
        'occurred_at' => 'datetime',
    ];

    /**
     * علاقة مع Subject (مورف)
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * علاقة مع Causer (مورف)
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * الحصول على رقم السجل الخارجي أو الداخلي
     */
    public function getLogIdAttribute()
    {
        return $this->external_log_id ?? $this->id;
    }

    /**
     * الحصول على اسم المستخدم/السبب
     */
    public function getCauserNameAttribute()
    {
        if ($this->causer_type && $this->causer_id) {
            return $this->causer_type . ' #' . $this->causer_id;
        }
        return 'غير معروف';
    }

    /**
     * الحصول على اسم الموضوع
     */
    public function getSubjectNameAttribute()
    {
        if ($this->subject_type && $this->subject_id) {
            return $this->subject_type . ' #' . $this->subject_id;
        }
        return 'غير محدد';
    }

    /**
     * الحصول على وصف الحدث
     */
    public function getEventDescriptionAttribute()
    {
        $descriptions = [
            'created' => 'إنشاء',
            'updated' => 'تحديث',
            'deleted' => 'حذف',
            'viewed' => 'عرض',
            'login' => 'تسجيل دخول',
            'logout' => 'تسجيل خروج',
            'restored' => 'استعادة',
            'retrieved' => 'استرجاع',
        ];

        return $descriptions[$this->event] ?? $this->event;
    }

    /**
     * تطبيق الفلاتر
     */
    public function scopeFilterByEvent($query, $event)
    {
        if ($event) {
            return $query->where('event', $event);
        }
        return $query;
    }

    public function scopeFilterByDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->where('occurred_at', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $query->where('occurred_at', '<=', Carbon::parse($endDate)->endOfDay());
        }
        return $query;
    }

    public function scopeFilterBySourceSystem($query, $sourceSystem)
    {
        if ($sourceSystem) {
            return $query->where('source_system', $sourceSystem);
        }
        return $query;
    }

    public function scopeFilterByBatchUuid($query, $batchUuid)
    {
        if ($batchUuid) {
            return $query->where('batch_uuid', $batchUuid);
        }
        return $query;
    }

    public function scopeFilterByCauser($query, $causerId, $causerType = null)
    {
        if ($causerId) {
            $query->where('causer_id', $causerId);
            if ($causerType) {
                $query->where('causer_type', $causerType);
            }
        }
        return $query;
    }

    public function scopeFilterBySubject($query, $subjectId, $subjectType = null)
    {
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
            if ($subjectType) {
                $query->where('subject_type', $subjectType);
            }
        }
        return $query;
    }
}
