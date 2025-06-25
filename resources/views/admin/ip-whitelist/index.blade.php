@extends('layouts.admin')

@section('title', 'عناوين IP - ' . $project->name)

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>إدارة عناوين IP</h2>
                <p class="text-muted">المشروع: {{ $project->name }}</p>
            </div>
            <div>
                <a href="{{ route('admin.projects.ip-whitelist.create', $project) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> IP جديد
                </a>
                <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
            </div>
        </div>

        <!-- IP Addresses -->
        @if($ips->count() > 0)
            <div class="row">
                @foreach($ips as $ip)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <code class="bg-light px-2 py-1 rounded">{{ $ip->ip_address }}</code>
                                    </h5>
                                    @if(strpos($ip->ip_address, '*') !== false)
                                        <small class="text-info">
                                            <i class="fas fa-star me-1"></i>Wildcard
                                        </small>
                                    @elseif(strpos($ip->ip_address, '/') !== false)
                                        <small class="text-info">
                                            <i class="fas fa-network-wired me-1"></i>CIDR
                                        </small>
                                    @else
                                        <small class="text-muted">
                                            <i class="fas fa-desktop me-1"></i>IP مباشر
                                        </small>
                                    @endif
                                </div>
                                @if($ip->status == 'active')
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                            </div>

                            <p class="text-muted mb-2">
                                {{ $ip->description ?? 'لا يوجد وصف' }}
                            </p>

                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                تم الإضافة: {{ $ip->created_at->format('Y-m-d') }}
                            </small>
                        </div>

                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('admin.projects.ip-whitelist.edit', [$project, $ip]) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="toggleStatus({{ $ip->id }}, '{{ $ip->status }}')"
                                        class="btn btn-outline-{{ $ip->status === 'active' ? 'warning' : 'success' }} btn-sm">
                                    <i class="fas fa-{{ $ip->status === 'active' ? 'pause' : 'play' }}"></i>
                                </button>
                                <button onclick="deleteIp({{ $ip->id }})"
                                        class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($ips->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $ips->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-shield-alt fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">لا توجد عناوين IP</h4>
                <p class="text-muted mb-4">ابدأ بإضافة عناوين IP للتحكم في الوصول للAPI</p>
                <a href="{{ route('admin.projects.ip-whitelist.create', $project) }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> إضافة عنوان IP جديد
                </a>
            </div>
        @endif

        <!-- Info Card -->
        <div class="alert alert-info mt-4">
            <h6 class="alert-heading">
                <i class="fas fa-info-circle me-2"></i>
                أنماط عناوين IP المدعومة
            </h6>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <strong>IP مباشر:</strong> <code>192.168.1.100</code>
                    <br><small>عنوان محدد واحد فقط</small>
                </div>
                <div class="col-md-4 mb-2">
                    <strong>CIDR:</strong> <code>192.168.1.0/24</code>
                    <br><small>مجموعة عناوين في شبكة</small>
                </div>
                <div class="col-md-4 mb-2">
                    <strong>Wildcard:</strong> <code>192.168.1.*</code>
                    <br><small>عناوين بنمط تحديد</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تأكيد الحذف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    هل أنت متأكد من رغبتك في حذف عنوان IP هذا؟ هذا الإجراء لا يمكن التراجع عنه.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteIp(ipId) {
            const form = document.getElementById('deleteForm');
            form.action = `/admin/projects/{{ $project->id }}/ip-whitelist/${ipId}`;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        function toggleStatus(ipId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const actionText = newStatus === 'active' ? 'تفعيل' : 'إلغاء تفعيل';

            if (confirm(`هل تريد ${actionText} عنوان IP هذا؟`)) {
                fetch(`/admin/ip-whitelist/${ipId}/toggle-status`, {
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
                });
            }
        }
    </script>
@endsection
