<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'project_id',
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
     * علاقة مع المشروع
     */
    public function project()
    {
        return $this->belongsTo( \App\Models\Project::class);
    }


}
