@extends('layouts.admin')

@section('title', 'تفاصيل السجل')

@section('content')
<div class="container-fluid">
    <!-- العنوان -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    تفاصيل السجل #{{ $log->external_log_id ?? $log->id }}
                </h1>
                <div>
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة للقائمة
                    </a>
                    @include('admin.logs.modal.delete_log', ['log' => $log])
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- المعلومات الأساسية -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        المعلومات الأساسية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">رقم السجل للمشروع:</label>
                            <div>
                                <span class="badge bg-dark fs-6">#{{ $log->external_log_id }}</span>
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">النظام المصدر:</label>
                            <div>
                                <span class="badge bg-primary fs-6">{{ $log->source_system }}</span>
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">اسم المشروع:</label>
                            <div>
                                @if($log->project_name)
                                    <span class="badge bg-info fs-6">{{ $log->project_name }}</span>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">نوع الحدث:</label>
                            <div>
                                @if($log->event)
                                    @php
                                        $eventColors = [
                                            'created' => 'bg-success',
                                            'updated' => 'bg-warning text-dark',
                                            'deleted' => 'bg-danger',
                                            'login' => 'bg-info',
                                            'logout' => 'bg-secondary'
                                        ];
                                        $eventNames = [
                                            'created' => 'إنشاء',
                                            'updated' => 'تحديث',
                                            'deleted' => 'حذف',
                                            'login' => 'تسجيل دخول',
                                            'logout' => 'تسجيل خروج'
                                        ];
                                    @endphp
                                    <span class="badge {{ $eventColors[$log->event] ?? 'bg-secondary' }} fs-6">
                                        {{ $eventNames[$log->event] ?? $log->event }}
                                    </span>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">اسم السجل:</label>
                            <div>
                                @if($log->log_name)
                                    <span class="badge bg-secondary fs-6">{{ $log->log_name }}</span>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">تاريخ الحدث:</label>
                            <div>
                                <div class="fw-semibold">{{ $log->occurred_at ? $log->occurred_at->format('Y-m-d') : $log->created_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $log->occurred_at ? $log->occurred_at->format('H:i:s') : $log->created_at->format('H:i:s') }}</small>
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">تاريخ الإرسال:</label>
                            <div>
                                <div class="fw-semibold">{{ $log->created_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">وصف الحدث:</label>
                            <div class="border p-2 rounded bg-light">
                                {{ $log->description }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- معلومات العنصر والمستخدم -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        العنصر المتأثر والمستخدم المسؤول
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- العنصر المتأثر -->
                        <div class="col-12">
                            <h6 class="fw-bold text-primary">العنصر المتأثر (Subject):</h6>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">نوع العنصر:</label>
                            <div>
                                @if($log->subject_type)
                                    @php
                                        $typeNames = [
                                            'App\Models\User' => 'مستخدم',
                                            'App\Models\Course' => 'كورس',
                                            'App\Models\Enrollment' => 'تسجيل في كورس'
                                        ];
                                    @endphp
                                    <span class="badge bg-success fs-6">{{ $typeNames[$log->subject_type] ?? class_basename($log->subject_type) }}</span>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">معرف العنصر:</label>
                            <div>
                                @if($log->subject_id)
                                    <code class="fs-6">#{{ $log->subject_id }}</code>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </div>
                        </div>

                        <!-- المستخدم المسؤول -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-warning">المستخدم المسؤول (Causer):</h6>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">نوع المستخدم:</label>
                            <div>
                                @if($log->causer_type)
                                    <span class="badge bg-warning text-dark fs-6">{{ class_basename($log->causer_type) }}</span>
                                @else
                                    <span class="text-muted">النظام (تلقائي)</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold">معرف المستخدم:</label>
                            <div>
                                @if($log->causer_id)
                                    <code class="fs-6">#{{ $log->causer_id }}</code>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </div>
                        </div>

                        @if($log->batch_uuid)
                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold">معرف المجموعة:</label>
                                <div>
                                    <code class="fs-6">{{ $log->batch_uuid }}</code>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- الخصائص والبيانات الإضافية -->
        @if($log->properties && !empty($log->properties))
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            البيانات والخصائص الإضافية
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(isset($log->properties['attributes']))
                            <div class="mb-4">
                                <h6 class="fw-bold text-success">القيم الجديدة:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>الحقل</th>
                                                <th>القيمة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($log->properties['attributes'] as $key => $value)
                                                <tr>
                                                    <td class="fw-bold">{{ $key }}</td>
                                                    <td>
                                                        @if(is_array($value) || is_object($value))
                                                            <code>{{ json_encode($value, JSON_UNESCAPED_UNICODE) }}</code>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if(isset($log->properties['old']))
                            <div class="mb-4">
                                <h6 class="fw-bold text-danger">القيم القديمة:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>الحقل</th>
                                                <th>القيمة السابقة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($log->properties['old'] as $key => $value)
                                                <tr>
                                                    <td class="fw-bold">{{ $key }}</td>
                                                    <td>
                                                        @if(is_array($value) || is_object($value))
                                                            <code>{{ json_encode($value, JSON_UNESCAPED_UNICODE) }}</code>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if(!isset($log->properties['attributes']) && !isset($log->properties['old']))
                            <div class="alert alert-info">
                                <h6 class="fw-bold">جميع الخصائص:</h6>
                                <pre class="mb-0"><code>{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- معلومات تقنية -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-code me-2"></i>
                        معلومات تقنية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">ID الداخلي:</label>
                            <div>
                                <code>{{ $log->id }}</code>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">تاريخ الإنشاء:</label>
                            <div>
                                <small>{{ $log->created_at->format('Y-m-d H:i:s') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">تاريخ التحديث:</label>
                            <div>
                                <small>{{ $log->updated_at->format('Y-m-d H:i:s') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">الفرق الزمني:</label>
                            <div>
                                <small class="text-muted">{{ $log->created_at->diffForHumans(['locale' => 'ar']) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge {
    font-size: 0.8em;
}
.card-body .row .col-12 h6 {
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 5px;
}
pre {
    max-height: 300px;
    overflow-y: auto;
}
</style>
@endpush
