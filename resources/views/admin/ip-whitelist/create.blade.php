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
                                عنوان IP <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('ip_address') is-invalid @enderror"
                                   id="ip_address"
                                   name="ip_address"
                                   value="{{ old('ip_address') }}"
                                   placeholder="192.168.1.100"
                                   required>
                            @error('ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                أدخل عنوان IP مباشر
                            </div>

                            <!-- أداة مساعدة للتحقق من IP الحالي -->
                            <div class="mt-2">
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="detectCurrentIp()">
                                    <i class="fas fa-location-arrow me-1"></i>
                                    استخدام IP الحالي
                                </button>
                                <span id="currentIpDisplay" class="text-muted ms-2"></span>
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
                    أمثلة لعناوين IP صحيحة
                </h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6>عناوين IPv4:</h6>
                        <button type="button" class="btn btn-outline-info btn-sm mb-2" onclick="showExample('direct')">
                            <code>192.168.1.100</code>
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm mb-2" onclick="showExample('local')">
                            <code>127.0.0.1</code>
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm mb-2" onclick="showExample('public')">
                            <code>203.0.113.1</code>
                        </button>
                        <br><small class="text-muted">عناوين IP محددة</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6>عناوين IPv6:</h6>
                        <button type="button" class="btn btn-outline-info btn-sm mb-2" onclick="showExample('ipv6')">
                            <code>2001:db8::1</code>
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm mb-2" onclick="showExample('ipv6local')">
                            <code>::1</code>
                        </button>
                        <br><small class="text-muted">عناوين IP الجيل الجديد</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// الحصول على IP الحالي للمستخدم
async function detectCurrentIp() {
    try {
        const response = await fetch('https://api.ipify.org?format=json');
        const data = await response.json();

        document.getElementById('ip_address').value = data.ip;
        document.getElementById('currentIpDisplay').textContent = `(${data.ip})`;

        // إضافة تأثير بصري
        const input = document.getElementById('ip_address');
        input.classList.add('border-success');
        setTimeout(() => {
            input.classList.remove('border-success');
        }, 2000);
    } catch (error) {
        console.error('فشل في الحصول على IP:', error);
        document.getElementById('currentIpDisplay').textContent = '(فشل في الحصول على IP)';
        document.getElementById('currentIpDisplay').classList.add('text-danger');
    }
}

// التحقق من صحة IP أثناء الكتابة
document.getElementById('ip_address').addEventListener('input', function() {
    const value = this.value.trim();
    const feedback = this.nextElementSibling;

    if (value) {
        // تحقق بسيط من النمط
        if (validateIpPattern(value)) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            // لا نضيف is-invalid هنا لتجنب التداخل مع validation errors
        }
    } else {
        this.classList.remove('is-valid', 'is-invalid');
    }
});

function validateIpPattern(ip) {
    // IPv4 مباشر
    if (/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ip)) {
        return true;
    }

    // IPv6 مباشر (تحقق بسيط)
    if (/^([0-9a-fA-F]{0,4}:){2,7}[0-9a-fA-F]{0,4}$/.test(ip) || ip === '::1') {
        return true;
    }

    return false;
}

// عرض مثال عند النقر على أحد الأزرار
function showExample(type) {
    const input = document.getElementById('ip_address');

    switch(type) {
        case 'direct':
            input.value = '192.168.1.100';
            break;
        case 'local':
            input.value = '127.0.0.1';
            break;
        case 'public':
            input.value = '203.0.113.1';
            break;
        case 'ipv6':
            input.value = '2001:db8::1';
            break;
        case 'ipv6local':
            input.value = '::1';
            break;
    }

    input.focus();
    input.dispatchEvent(new Event('input'));
}
</script>

@endsection
