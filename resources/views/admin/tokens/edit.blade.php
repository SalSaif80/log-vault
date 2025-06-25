@extends('layouts.admin')

@section('title', 'تعديل التوكن')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>تعديل التوكن: {{ $token->name }}</h2>
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
                    <form action="{{ route('admin.projects.tokens.update', [$project, $token]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-tag me-2"></i>
                                اسم التوكن
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $token->name) }}"
                                   placeholder="مثال: Production API Token"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-toggle-on me-2"></i>
                                الحالة
                            </label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status"
                                    name="status">
                                <option value="active" {{ old('status', $token->status) == 'active' ? 'selected' : '' }}>
                                    نشط
                                </option>
                                <option value="inactive" {{ old('status', $token->status) == 'inactive' ? 'selected' : '' }}>
                                    غير نشط
                                </option>
                            </select>
                            @error('status')
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
                                   value="{{ old('expires_at', $token->expires_at ? $token->expires_at->format('Y-m-d') : '') }}"
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
                                <i class="fas fa-save"></i> حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Token Info -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">معلومات التوكن</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>معرف التوكن:</strong> ***{{ substr($token->token, -8) }}
                                </li>
                                <li class="mb-2">
                                    <strong>تاريخ الإنشاء:</strong> {{ $token->created_at->format('Y-m-d H:i') }}
                                </li>
                                <li class="mb-2">
                                    <strong>آخر تحديث:</strong> {{ $token->updated_at->format('Y-m-d H:i') }}
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>آخر استخدام:</strong>
                                    {{ $token->last_used_at ? $token->last_used_at->format('Y-m-d H:i') : 'لم يُستخدم' }}
                                </li>
                                <li class="mb-2">
                                    <strong>الحالة الحالية:</strong>
                                    @if($token->status === 'active')
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-secondary">غير نشط</span>
                                    @endif
                                </li>
                                <li class="mb-2">
                                    <strong>ملاحظة:</strong> لا يمكن تغيير التوكن نفسه، فقط إعداداته
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
