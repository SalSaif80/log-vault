@extends('layouts.admin')

@section('title', 'IP جديد')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>إضافة IP جديد</h2>
            <p class="text-muted">المشروع: {{ $project->name }}</p>
        </div>
        <a href="{{ route('admin.projects.ip-whitelist.index', $project) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>

    <!-- Form -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.projects.ip-whitelist.store', $project) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="ip_address" class="form-label">
                                <i class="fas fa-network-wired me-2"></i>
                                عنوان IP
                            </label>
                            <input type="text"
                                   class="form-control @error('ip_address') is-invalid @enderror"
                                   id="ip_address"
                                   name="ip_address"
                                   value="{{ old('ip_address') }}"
                                   placeholder="192.168.1.100 أو 192.168.1.* أو 192.168.1.0/24"
                                   required>
                            @error('ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                يمكنك استخدام IP مباشر، Wildcard، أو CIDR notation
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-2"></i>
                                الوصف (اختياري)
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="مثال: سيرفر الإنتاج">{{ old('description') }}</textarea>
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
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                    نشط
                                </option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                    غير نشط
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.projects.ip-whitelist.index', $project) }}" class="btn btn-outline-secondary me-md-2">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إضافة IP
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Examples -->
            <div class="alert alert-info mt-4">
                <h6 class="alert-heading">
                    <i class="fas fa-lightbulb me-2"></i>
                    أمثلة
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled mb-0">
                            <li><code>192.168.1.100</code> - IP واحد</li>
                            <li><code>192.168.1.*</code> - نمط Wildcard</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled mb-0">
                            <li><code>10.0.0.0/8</code> - شبكة CIDR</li>
                            <li><code>2001:db8::1</code> - عنوان IPv6</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
