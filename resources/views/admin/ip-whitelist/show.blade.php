@extends('layouts.admin')

@section('title', 'تفاصيل عنوان IP')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>تفاصيل عنوان IP</h2>
            <p class="text-muted">المشروع: {{ $project->name }}</p>
        </div>
        <div>
            <a href="{{ route('admin.projects.ip-whitelist.edit', [$project, $ipWhitelist]) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>تعديل
            </a>
            <a href="{{ route('admin.projects.ip-whitelist.index', $project) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-2"></i>العودة
            </a>
        </div>
    </div>

    <!-- معلومات IP -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        معلومات عنوان IP
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>عنوان IP:</strong></td>
                                    <td><code class="bg-light px-2 py-1 rounded">{{ $ipWhitelist->ip_address }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>الحالة:</strong></td>
                                    <td>
                                        <span class="badge {{ $ipWhitelist->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $ipWhitelist->status === 'active' ? 'نشط' : 'معطل' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>نوع النمط:</strong></td>
                                    <td>
                                        @php
                                            $patternType = $ipWhitelist->pattern_type;
                                            $badgeClass = match($patternType) {
                                                'direct' => 'bg-success',
                                                'wildcard' => 'bg-info',
                                                'cidr' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                            $patternText = match($patternType) {
                                                'direct' => 'IP مباشر',
                                                'wildcard' => 'Wildcard',
                                                'cidr' => 'CIDR',
                                                default => 'غير محدد'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $patternText }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>تاريخ الإضافة:</strong></td>
                                    <td>{{ $ipWhitelist->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>آخر تحديث:</strong></td>
                                    <td>{{ $ipWhitelist->updated_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>المشروع:</strong></td>
                                    <td>
                                        <a href="{{ route('admin.projects.show', $project) }}" class="text-decoration-none">
                                            {{ $project->name }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($ipWhitelist->description)
                        <div class="mt-3">
                            <strong>الوصف:</strong>
                            <p class="text-muted mb-0">{{ $ipWhitelist->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- أدوات التحكم -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>
                        أدوات التحكم
                    </h5>
                </div>
                <div class="card-body">
                    <!-- تغيير الحالة -->
                    <div class="mb-3">
                        <button class="btn btn-{{ $ipWhitelist->status === 'active' ? 'warning' : 'success' }} w-100"
                                onclick="toggleStatus()">
                            <i class="fas fa-{{ $ipWhitelist->status === 'active' ? 'pause' : 'play' }} me-2"></i>
                            {{ $ipWhitelist->status === 'active' ? 'تعطيل' : 'تفعيل' }}
                        </button>
                    </div>

                    <!-- تعديل -->
                    <div class="mb-3">
                        <a href="{{ route('admin.projects.ip-whitelist.edit', [$project, $ipWhitelist]) }}"
                           class="btn btn-primary w-100">
                            <i class="fas fa-edit me-2"></i>تعديل
                        </a>
                    </div>

                    <!-- حذف -->
                    <div class="mb-3">
                        <button class="btn btn-danger w-100" onclick="confirmDelete()">
                            <i class="fas fa-trash me-2"></i>حذف
                        </button>
                    </div>

                    <hr>

                    <!-- اختبار IP -->
                    <div class="mb-3">
                        <label for="testIp" class="form-label">اختبار IP:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="testIp" placeholder="192.168.1.1">
                            <button class="btn btn-outline-secondary" onclick="testIp()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div id="testResult" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- تفاصيل النمط -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-network-wired me-2"></i>
                        تفاصيل النمط
                    </h5>
                </div>
                <div class="card-body">
                    @if($ipWhitelist->pattern_type === 'direct')
                        <div class="alert alert-info">
                            <h6><i class="fas fa-desktop me-2"></i>IP مباشر</h6>
                            <p class="mb-0">هذا النمط يسمح بعنوان IP واحد محدد فقط: <code>{{ $ipWhitelist->ip_address }}</code></p>
                        </div>
                    @elseif($ipWhitelist->pattern_type === 'wildcard')
                        <div class="alert alert-info">
                            <h6><i class="fas fa-star me-2"></i>نمط Wildcard</h6>
                            <p class="mb-2">هذا النمط يسمح بمجموعة من عناوين IP حسب النمط: <code>{{ $ipWhitelist->ip_address }}</code></p>
                            <p class="mb-0"><strong>أمثلة على عناوين مسموحة:</strong></p>
                            @php
                                $pattern = $ipWhitelist->ip_address;
                                $examples = [];
                                if (str_ends_with($pattern, '*')) {
                                    $base = rtrim($pattern, '*');
                                    $examples = [
                                        $base . '1',
                                        $base . '100',
                                        $base . '254'
                                    ];
                                }
                            @endphp
                            @if(!empty($examples))
                                <ul class="mb-0">
                                    @foreach($examples as $example)
                                        <li><code>{{ $example }}</code></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @elseif($ipWhitelist->pattern_type === 'cidr')
                        <div class="alert alert-info">
                            <h6><i class="fas fa-network-wired me-2"></i>شبكة CIDR</h6>
                            <p class="mb-2">هذا النمط يسمح بنطاق من عناوين IP في الشبكة: <code>{{ $ipWhitelist->ip_address }}</code></p>
                            @php
                                [$subnet, $mask] = explode('/', $ipWhitelist->ip_address);
                                $maskInt = intval($mask);
                                $totalIps = pow(2, 32 - $maskInt);
                            @endphp
                            <p class="mb-0">
                                <strong>الشبكة:</strong> <code>{{ $subnet }}</code> |
                                <strong>القناع:</strong> <code>/{{ $mask }}</code> |
                                <strong>عدد العناوين:</strong> {{ number_format($totalIps) }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- معلومات إضافية -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        معلومات إضافية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>معلومات النظام:</h6>
                            <ul class="list-unstyled">
                                <li><strong>المعرف:</strong> #{{ $ipWhitelist->id }}</li>
                                <li><strong>مضاف منذ:</strong> {{ $ipWhitelist->created_at->diffForHumans() }}</li>
                                <li><strong>آخر تحديث:</strong> {{ $ipWhitelist->updated_at->diffForHumans() }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>إجراءات سريعة:</h6>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.projects.ip-whitelist.create', $project) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>إضافة جديد
                                </a>
                                <a href="{{ route('admin.projects.ip-whitelist.index', $project) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-list me-1"></i>عرض الكل
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                هل أنت متأكد من رغبتك في حذف عنوان IP هذا؟
                <br>
                <strong>عنوان IP:</strong> <code>{{ $ipWhitelist->ip_address }}</code>
                <br><br>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    هذا الإجراء لا يمكن التراجع عنه!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('admin.projects.ip-whitelist.destroy', [$project, $ipWhitelist]) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>حذف نهائياً
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStatus() {
    if (confirm('هل تريد تغيير حالة عنوان IP هذا؟')) {
        fetch(`/admin/ip-whitelist/{{ $ipWhitelist->id }}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ في تغيير حالة عنوان IP');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في الاتصال');
        });
    }
}

function confirmDelete() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function testIp() {
    const testIp = document.getElementById('testIp').value.trim();
    const resultDiv = document.getElementById('testResult');

    if (!testIp) {
        resultDiv.innerHTML = '<div class="alert alert-warning">يرجى إدخال عنوان IP للاختبار</div>';
        return;
    }

    resultDiv.innerHTML = '<div class="text-muted"><i class="fas fa-spinner fa-spin me-2"></i>جاري الاختبار...</div>';

    fetch(`{{ route('admin.projects.ip-whitelist.test', $project) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            ip: testIp,
            project_id: {{ $project->id }}
        })
    })
    .then(response => response.json())
    .then(data => {
        const alertClass = data.allowed ? 'alert-success' : 'alert-danger';
        const icon = data.allowed ? 'fa-check-circle' : 'fa-times-circle';
        resultDiv.innerHTML = `
            <div class="alert ${alertClass}">
                <i class="fas ${icon} me-2"></i>
                ${data.message}
            </div>
        `;
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = '<div class="alert alert-danger">حدث خطأ في الاختبار</div>';
    });
}

// تعبئة IP الحالي كمثال
document.addEventListener('DOMContentLoaded', function() {
    fetch('https://api.ipify.org?format=json')
        .then(response => response.json())
        .then(data => {
            document.getElementById('testIp').placeholder = data.ip;
        })
        .catch(() => {
            // في حالة فشل الحصول على IP الخارجي
        });
});
</script>

@endsection
