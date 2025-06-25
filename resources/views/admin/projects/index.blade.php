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

    <!-- Projects Grid -->
    @if($projects->count() > 0)
        <div class="row">
            @foreach($projects as $project)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">{{ $project->name }}</h5>
                            @if($project->status === 'active')
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
                                    <div class="fw-bold">{{ $project->logs_count }}</div>
                                    <small class="text-muted">سجل</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat">
                                    <i class="fas fa-calendar text-info"></i>
                                    <div class="fw-bold">{{ $project->created_at->format('m/d') }}</div>
                                    <small class="text-muted">تاريخ الإنشاء</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i>
                                عرض
                            </a>
                            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-edit"></i>
                                تعديل
                            </a>
                            <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المشروع؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                    حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if(method_exists($projects, 'hasPages') && $projects->hasPages())
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
