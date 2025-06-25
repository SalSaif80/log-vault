@extends('layouts.admin')

@section('title', 'تعديل عنوان IP - ' . $project->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">تعديل عنوان IP</h1>
            <p class="text-muted">المشروع: {{ $project->name }}</p>
        </div>
        <a href="{{ route('admin.projects.ip-whitelist.index', $project) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> العودة
        </a>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">تعديل بيانات عنوان IP</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.projects.ip-whitelist.update', [$project, $ipWhitelist]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="ip_address">عنوان IP <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('ip_address') is-invalid @enderror"
                                   id="ip_address"
                                   name="ip_address"
                                   value="{{ old('ip_address', $ipWhitelist->ip_address) }}"
                                   placeholder="192.168.1.100 أو 192.168.1.0/24 أو 192.168.1.*">
                            @error('ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                يمكنك استخدام عنوان IP مباشر، CIDR notation، أو Wildcard pattern
                            </small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">الحالة <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror"
                                    id="status"
                                    name="status">
                                <option value="active" {{ old('status', $ipWhitelist->status) == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ old('status', $ipWhitelist->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">الوصف</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description"
                              name="description"
                              rows="3"
                              placeholder="وصف مختصر لعنوان IP (اختياري)">{{ old('description', $ipWhitelist->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> حفظ التغييرات
                    </button>
                    <a href="{{ route('admin.projects.ip-whitelist.index', $project) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- IP Info -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-info">معلومات عنوان IP</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <ul class="mb-0">
                        <li><strong>عنوان IP الحالي:</strong> <code>{{ $ipWhitelist->ip_address }}</code></li>
                        <li><strong>تاريخ الإضافة:</strong> {{ $ipWhitelist->created_at->format('Y-m-d H:i') }}</li>
                        <li><strong>آخر تحديث:</strong> {{ $ipWhitelist->updated_at->format('Y-m-d H:i') }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="mb-0">
                        <li><strong>الحالة الحالية:</strong>
                            @if($ipWhitelist->status === 'active')
                                <span class="badge badge-success">نشط</span>
                            @else
                                <span class="badge badge-danger">غير نشط</span>
                            @endif
                        </li>
                        <li><strong>نوع النمط:</strong>
                            @if(strpos($ipWhitelist->ip_address, '*') !== false)
                                <span class="badge badge-info">Wildcard</span>
                            @elseif(strpos($ipWhitelist->ip_address, '/') !== false)
                                <span class="badge badge-info">CIDR</span>
                            @else
                                <span class="badge badge-success">IP مباشر</span>
                            @endif
                        </li>
                        <li><strong>المشروع:</strong> {{ $project->name }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// مساعد لتحقق من صحة IP أثناء الكتابة
document.getElementById('ip_address').addEventListener('input', function() {
    const value = this.value.trim();

    if (value) {
        // تحقق بسيط من النمط
        if (value.includes('*') || value.includes('/') || value.match(/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/)) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    } else {
        this.classList.remove('is-valid', 'is-invalid');
    }
});
</script>
@endsection
