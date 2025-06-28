@extends('layouts.admin')

@section('title', 'لوحة التحكم')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>مرحباً {{ Auth::user()->name }}</h2>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> مشروع جديد
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-folder fa-2x text-primary mb-2"></i>
                    <h3 class="text-primary">{{ number_format($stats['total_projects']) }}</h3>
                    <p class="text-muted mb-0">إجمالي المشاريع</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h3 class="text-success">{{ number_format($stats['active_projects']) }}</h3>
                    <p class="text-muted mb-0">المشاريع النشطة</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-key fa-2x text-info mb-2"></i>
                    <h3 class="text-info">{{ number_format($stats['total_tokens']) }}</h3>
                    <p class="text-muted mb-0">توكنات API</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Activity Overview -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-day fa-2x text-success mb-2"></i>
                    <h4 class="text-success">{{ number_format($stats['today_logs']) }}</h4>
                    <p class="text-muted mb-0">سجلات اليوم</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-week fa-2x text-info mb-2"></i>
                    <h4 class="text-info">{{ number_format($stats['week_logs']) }}</h4>
                    <p class="text-muted mb-0">سجلات الأسبوع</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-server fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary">{{ number_format($stats['source_systems']) }}</h4>
                    <p class="text-muted mb-0">المشاريع التي ترسل السجلات</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Projects -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-folder me-2"></i>
                        المشاريع الحديثة
                    </h5>
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-primary btn-sm">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    @if($recent_projects->count() > 0)
                        @foreach($recent_projects as $project)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <strong>{{ $project->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $project->logs_count }} سجل
                                        @if($project->status === 'active')
                                            <span class="badge bg-success ms-1">نشط</span>
                                        @else
                                            <span class="badge bg-secondary ms-1">غير نشط</span>
                                        @endif
                                    </small>
                                </div>
                                <div>
                                    <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد مشاريع</h5>
                            <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إنشاء أول مشروع
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        آخر السجلات
                    </h5>
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-primary btn-sm">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body">
                    @if($recent_logs->count() > 0)
                        @foreach($recent_logs as $log)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <span class="badge bg-info">{{ $log->event ?? 'غير محدد' }}</span>
                                    @if($log->source_system)
                                        <small class="text-muted ms-2">{{ $log->source_system }}</small>
                                    @endif
                                    <br>
                                    <small class="text-muted">
                                        {{ $log->created_at->diffForHumans(['locale' => 'ar']) }}
                                        @if($log->description)
                                            • {{ Str::limit($log->description, 30) }}
                                        @endif
                                    </small>
                                </div>
                                <div>
                                    <a href="{{ route('admin.logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد سجلات بعد</h5>
                            <p class="text-muted">سيتم عرض السجلات هنا عند وصولها من المشاريع</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-rocket me-2"></i>
                        إجراءات سريعة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.projects.create') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-plus fa-2x mb-2 d-block"></i>
                                إنشاء مشروع جديد
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-danger w-100">
                                <i class="fas fa-list-alt fa-2x mb-2 d-block"></i>
                                استعراض السجلات
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
