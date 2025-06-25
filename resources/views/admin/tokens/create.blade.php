@extends('layouts.admin')

@section('title', 'توكن جديد')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>إنشاء توكن جديد</h2>
            <p class="text-muted">المشروع: {{ $project->name }}</p>
        </div>
        <a href="{{ route('admin.projects.tokens.index', $project) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>

    <!-- Form -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.projects.tokens.store', $project) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-tag me-2"></i>
                                اسم التوكن
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="مثال: Production API Token"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="expires_at" class="form-label">
                                <i class="fas fa-calendar me-2"></i>
                                تاريخ الانتهاء (اختياري)
                            </label>
                            <input type="date"
                                   class="form-control @error('expires_at') is-invalid @enderror"
                                   id="expires_at"
                                   name="expires_at"
                                   value="{{ old('expires_at') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            <div class="form-text">اتركه فارغاً للتوكن الدائم</div>
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.projects.tokens.index', $project) }}" class="btn btn-outline-secondary me-md-2">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إنشاء التوكن
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info -->
            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>تنبيه مهم:</strong> التوكن سيظهر مرة واحدة فقط بعد إنشاؤه - احرص على نسخه وحفظه في مكان آمن.
            </div>
        </div>
    </div>
</div>
@endsection
