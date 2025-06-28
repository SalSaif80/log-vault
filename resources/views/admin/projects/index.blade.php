@extends('layouts.admin')

@section('title', 'المشاريع')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>المشاريع</h2>
            <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> مشروع جديد
            </a>
        </div>

        <!-- إحصائيات سريعة -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">إجمالي المشاريع</h5>
                                <h3 class="mb-0">{{ number_format($stats['total_projects']) }}</h3>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-folder fa-2x opacity-75"></i>
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
                                <h5 class="card-title mb-0">مشاريع نشطة</h5>
                                <h3 class="mb-0">{{ number_format($stats['active_projects']) }}</h3>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                                <h5 class="card-title mb-0">مشاريع غير نشطة</h5>
                                <h3 class="mb-0">{{ number_format($stats['inactive_projects']) }}</h3>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-pause-circle fa-2x opacity-75"></i>
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
                                <h5 class="card-title mb-0">إجمالي السجلات</h5>
                                <h3 class="mb-0">{{ number_format($stats['total_logs']) }}</h3>

                            </div>
                            <div class="ms-3">
                                <i class="fas fa-list-alt fa-2x opacity-75"></i>
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
                <form method="GET" action="{{ route('admin.projects.index') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">حالة المشروع</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">بحث في المشاريع</label>
                            <input type="text" name="search" id="search" class="form-control"
                                value="{{ request('search') }}" placeholder="البحث في اسم المشروع أو الوصف...">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                بحث وتصفية
                            </button>
                            <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                إلغاء الفلاتر
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Projects Grid -->
        @if ($projects->count() > 0)
            <div class="row">
                @foreach ($projects as $project)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0">{{ $project->name }}</h5>
                                    @if ($project->status === 'active')
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-secondary">غير نشط</span>
                                    @endif
                                </div>

                                <p class="text-muted mb-3">{{ $project->description ?? 'لا يوجد وصف' }}</p>

                                <div class="mb-3">
                                    <small class="text-muted">الرمز: </small>
                                    <code>{{ $project->slug }}</code>
                                </div>

                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <div class="stat">
                                            <i class="fas fa-list-alt text-primary"></i>
                                            <div class="fw-bold">{{ number_format($project->logs_count) }}</div>
                                            <small class="text-muted">سجل</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat">
                                            <i class="fas fa-calendar text-info"></i>
                                            <div class="fw-bold">{{ $project->created_at->format('d/m/Y') }}</div>
                                            <small class="text-muted">تاريخ الإنشاء</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-transparent">
                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('admin.projects.show', $project) }}"
                                        class=" btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                        عرض
                                    </a>
                                    &nbsp;
                                    <a href="{{ route('admin.logs.index', ['project_id' => $project->id]) }}"
                                        class=" btn btn-outline-success btn-sm">
                                        <i class="fas fa-list-alt"></i>
                                        السجلات
                                    </a>
                                    &nbsp;
                                    <a href="{{ route('admin.projects.edit', $project) }}"
                                        class=" btn btn-outline-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                        تعديل
                                    </a>
                                    &nbsp;
                                    @if ($project->logs()->count() <= 0)
                                        @include('admin.projects.modal.delete_project', ['project' => $project])
                                    @else
                                        @include('admin.projects.modal.cannot_delete_project', ['project' => $project])
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if (method_exists($projects, 'hasPages') && $projects->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $projects->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">لا توجد مشاريع</h4>
                <p class="text-muted mb-4">ابدأ بإنشاء أول مشروع لك</p>
                <a href="{{ route('admin.projects.create') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> إنشاء مشروع جديد
                </a>
            </div>
        @endif
    </div>
@endsection
