@extends('layouts.admin')

@section('title', 'إدارة السجلات')

@section('content')
<div class="container-fluid">
    <!-- العنوان والإحصائيات -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">
                    <i class="fas fa-list-alt me-2"></i>
                    إدارة السجلات
                </h1>
                <div class="btn-group">
                    <a href="{{ route('admin.logs.export', request()->query()) }}" class="btn btn-success">
                        <i class="fas fa-download me-1"></i>
                        تصدير السجلات
                    </a>
                    <button type="button" class="btn btn-info" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-1"></i>
                        تحديث
                    </button>
                </div>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">إجمالي السجلات</h5>
                                    <h3 class="mb-0">{{ number_format($stats['total_logs']) }}</h3>
                                </div>
                                <div class="ms-3">
                                    <i class="fas fa-list fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">سجلات اليوم</h5>
                                    <h3 class="mb-0">{{ number_format($stats['today_logs']) }}</h3>
                                </div>
                                <div class="ms-3">
                                    <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">سجلات الأسبوع</h5>
                                    <h3 class="mb-0">{{ number_format($stats['week_logs']) }}</h3>
                                </div>
                                <div class="ms-3">
                                    <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">الأنظمة النشطة</h5>
                                    <h3 class="mb-0">{{ number_format($stats['active_systems']) }}</h3>
                                </div>
                                <div class="ms-3">
                                    <i class="fas fa-server fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- فلاتر البحث -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>
                فلاتر البحث والتصفية
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.logs.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="source_system" class="form-label">النظام المصدر</label>
                        <select name="source_system" id="source_system" class="form-select">
                            <option value="">جميع الأنظمة</option>
                            @foreach($sourceSystems as $system)
                                <option value="{{ $system }}" {{ request('source_system') == $system ? 'selected' : '' }}>
                                    {{ $system }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="project_name" class="form-label">اسم المشروع</label>
                        <select name="project_name" id="project_name" class="form-select">
                            <option value="">جميع المشاريع</option>
                            @php
                                $projects = $logs->pluck('project_name')->unique()->filter()->sort();
                            @endphp
                            @foreach($projects as $project)
                                <option value="{{ $project }}" {{ request('project_name') == $project ? 'selected' : '' }}>
                                    {{ $project }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="event" class="form-label">نوع الحدث</label>
                        <select name="event" id="event" class="form-select">
                            <option value="">جميع الأحداث</option>
                            <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>إنشاء</option>
                            <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>تحديث</option>
                            <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>حذف</option>
                            <option value="login" {{ request('event') == 'login' ? 'selected' : '' }}>تسجيل دخول</option>
                            <option value="logout" {{ request('event') == 'logout' ? 'selected' : '' }}>تسجيل خروج</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="subject_type" class="form-label">نوع العنصر</label>
                        <select name="subject_type" id="subject_type" class="form-select">
                            <option value="">جميع الأنواع</option>
                            <option value="App\Models\User" {{ request('subject_type') == 'App\Models\User' ? 'selected' : '' }}>مستخدم</option>
                            <option value="App\Models\Course" {{ request('subject_type') == 'App\Models\Course' ? 'selected' : '' }}>كورس</option>
                            <option value="App\Models\Enrollment" {{ request('subject_type') == 'App\Models\Enrollment' ? 'selected' : '' }}>تسجيل</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-8">
                        <label for="search" class="form-label">بحث عام في النص</label>
                        <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="البحث في الوصف، أسماء المستخدمين، أو تفاصيل الأحداث...">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>
                            بحث وتصفية
                        </button>
                        <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            إلغاء الفلاتر
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- قائمة السجلات -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                السجلات ({{ $logs->total() }} سجل)
            </h5>
        </div>
        <div class="card-body p-0">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="8%">رقم السجل</th>
                                <th width="8%">رقم السجل الخارجي</th>
                                <th width="12%">المشروع/النظام</th>
                                <th width="35%">تفاصيل الحدث</th>
                                <th width="15%">العنصر المتأثر</th>
                                <th width="12%">المستخدم المسؤول</th>
                                <th width="12%">التاريخ والوقت</th>
                                <th width="6%">العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>
                                        <span class="badge bg-dark fs-6">#{{ $log->id }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark fs-6">#{{ $log->external_log_id }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge bg-primary mb-1">{{ $log->source_system }}</span>
                                            @if($log->project_name)
                                                <br>
                                                <small class="text-muted">{{ $log->project_name }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
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
                                                        'login' => 'دخول',
                                                        'logout' => 'خروج'
                                                    ];
                                                @endphp
                                                <span class="badge {{ $eventColors[$log->event] ?? 'bg-secondary' }} mb-1">
                                                    {{ $eventNames[$log->event] ?? $log->event }}
                                                </span>
                                            @endif
                                            <div class="mt-1">
                                                <small>{{ $log->description }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($log->subject_type)
                                            @php
                                                $typeNames = [
                                                    'App\Models\User' => 'مستخدم',
                                                    'App\Models\Course' => 'كورس',
                                                    'App\Models\Enrollment' => 'تسجيل'
                                                ];
                                            @endphp
                                            <div>
                                                <span class="badge bg-light text-dark">{{ $typeNames[$log->subject_type] ?? class_basename($log->subject_type) }}</span>
                                                @if($log->subject_id)
                                                    <br><small class="text-muted">ID: {{ $log->subject_id }}</small>
                                                @endif
                                                @if($log->properties && isset($log->properties['attributes']))
                                                    @if(isset($log->properties['attributes']['name']))
                                                        <br><small class="fw-bold">{{ $log->properties['attributes']['name'] }}</small>
                                                    @elseif(isset($log->properties['attributes']['course_name']))
                                                        <br><small class="fw-bold">{{ $log->properties['attributes']['course_name'] }}</small>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->causer_type && $log->causer_id)
                                            <div>
                                                <span class="badge bg-secondary">مستخدم</span>
                                                <br><small class="text-muted">ID: {{ $log->causer_id }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">النظام</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $log->occurred_at ? $log->occurred_at->format('Y-m-d') : $log->created_at->format('Y-m-d') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $log->occurred_at ? $log->occurred_at->format('H:i:s') : $log->created_at->format('H:i:s') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.logs.show', $log) }}" class="btn btn-outline-primary" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.logs.destroy', $log) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger"
                                                        onclick="return confirm('هل أنت متأكد من حذف هذا السجل؟')"
                                                        title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد سجلات</h5>
                    <p class="text-muted">لم يتم العثور على سجلات تطابق معايير البحث المحددة.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table td {
    vertical-align: middle;
}
.badge {
    font-size: 0.75em;
}
</style>
@endpush
