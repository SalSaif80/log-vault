@extends('layouts.admin')

@section('title', 'تعديل المشروع')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>تعديل المشروع: {{ $project->name }}</h2>
        <div>
            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-outline-info">
                <i class="fas fa-eye"></i> عرض
            </a>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right"></i> العودة
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.projects.update', $project) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-tag me-2"></i>
                                اسم المشروع
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $project->name) }}"
                                   placeholder="مثال: Mini School"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-2"></i>
                                الوصف
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="وصف مختصر للمشروع">{{ old('description', $project->description) }}</textarea>
                            @error('description')
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
                                <option value="active" {{ old('status', $project->status) == 'active' ? 'selected' : '' }}>
                                    نشط
                                </option>
                                <option value="inactive" {{ old('status', $project->status) == 'inactive' ? 'selected' : '' }}>
                                    غير نشط
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-outline-secondary me-md-2">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Project Info -->
            <div class="alert alert-info mt-4">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    معلومات إضافية
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled mb-0">
                            <li><strong>الرمز:</strong> <code>{{ $project->slug }}</code></li>
                            <li><strong>التوكنات:</strong> {{ $project->tokens()->count() }}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled mb-0">
                            <li><strong>عناوين IP:</strong> {{ $project->ipWhitelist()->count() }}</li>
                            <li><strong>آخر تحديث:</strong> {{ $project->updated_at->format('Y-m-d') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
