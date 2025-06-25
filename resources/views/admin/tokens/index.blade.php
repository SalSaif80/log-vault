@extends('layouts.admin')

@section('title', 'التوكنات - ' . $project->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>إدارة التوكنات</h2>
            <p class="text-muted">المشروع: {{ $project->name }}</p>
        </div>
        <div>
            <a href="{{ route('admin.projects.tokens.create', $project) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> توكن جديد
            </a>
            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right"></i> العودة
            </a>
        </div>
    </div>

    <!-- New Token Alert -->
    @if(session('plain_token'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-triangle me-2"></i>
                التوكن الجديد
            </h5>
            <p class="mb-2">تم إنشاء التوكن بنجاح. انسخ التوكن الآن لأنه لن يظهر مرة أخرى:</p>
            <div class="input-group">
                <input type="text" class="form-control" value="{{ session('plain_token') }}" readonly id="newToken">
                <button class="btn btn-outline-secondary" type="button" onclick="copyToken()">
                    <i class="fas fa-copy"></i> نسخ
                </button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tokens -->
    @if($tokens->count() > 0)
        <div class="row">
            @foreach($tokens as $token)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">{{ $token->name }}</h5>
                            @if($token->status === 'active')
                                <span class="badge bg-success">نشط</span>
                            @else
                                <span class="badge bg-secondary">غير نشط</span>
                            @endif
                        </div>

                        <p class="text-muted mb-2">
                            <i class="fas fa-key me-1"></i>
                            ***{{ substr($token->token, -8) }}
                        </p>

                        <div class="mb-3">
                            @if($token->expires_at)
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    ينتهي: {{ $token->expires_at->format('Y-m-d') }}
                                    <span class="@if($token->expires_at->isPast()) text-danger @elseif($token->expires_at->diffInDays() <= 7) text-warning @endif">
                                        ({{ $token->expires_at->diffForHumans() }})
                                    </span>
                                </small>
                            @else
                                <small class="text-muted">
                                    <i class="fas fa-infinity me-1"></i>
                                    لا ينتهي
                                </small>
                            @endif
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                آخر استخدام: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'لم يُستخدم' }}
                            </small>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('admin.projects.tokens.edit', [$project, $token]) }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($token->status === 'active')
                                <button onclick="revokeToken({{ $token->id }})"
                                        class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @endif
                            <button onclick="deleteToken({{ $token->id }})"
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
        @if($tokens->hasPages())
            <div class="d-flex justify-content-center">
                {{ $tokens->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-5">
            <i class="fas fa-key fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">لا توجد توكنات</h4>
            <p class="text-muted mb-4">ابدأ بإنشاء توكن جديد للوصول إلى API</p>
            <a href="{{ route('admin.projects.tokens.create', $project) }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i> إنشاء توكن جديد
            </a>
        </div>
    @endif
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
                هل أنت متأكد من رغبتك في حذف هذا التوكن؟ هذا الإجراء لا يمكن التراجع عنه.
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
function copyToken() {
    const tokenInput = document.getElementById('newToken');
    tokenInput.select();
    document.execCommand('copy');

    // تغيير النص مؤقتاً للإشارة للنسخ
    const button = event.target.closest('button');
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
    setTimeout(() => {
        button.innerHTML = originalHtml;
    }, 2000);
}

function deleteToken(tokenId) {
    const form = document.getElementById('deleteForm');
    form.action = `/admin/projects/{{ $project->id }}/tokens/${tokenId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function revokeToken(tokenId) {
    if (confirm('هل تريد إلغاء هذا التوكن؟')) {
        fetch(`/admin/tokens/${tokenId}/revoke`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('حدث خطأ أثناء إلغاء التوكن');
            }
        });
    }
}
</script>
@endsection
