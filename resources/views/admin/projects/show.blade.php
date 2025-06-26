@extends('layouts.admin')

@section('title', 'تفاصيل المشروع: ' . $project->name)

@section('content')
<div class="container-fluid">
    <!-- العنوان -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-folder me-2"></i>
                    {{ $project->name }}
                </h1>
                <div>
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة للقائمة
                    </a>
                    <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>
                        تعديل
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- عرض رسائل النجاح والخطأ -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('new_token'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-key me-2"></i>تم إنشاء التوكن بنجاح!</h5>
            <p><strong>مهم:</strong> احفظ هذا التوكن الآن! لن تتمكن من رؤيته مرة أخرى.</p>
            <div class="input-group">
                <input type="text" class="form-control" value="{{ session('new_token') }}" readonly id="newTokenValue">
                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- معلومات المشروع -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        معلومات المشروع
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>الاسم:</strong> {{ $project->name }}</p>
                            <p><strong>الرمز:</strong> <code>{{ $project->slug }}</code></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>الحالة:</strong>
                                <span class="badge {{ $project->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $project->status === 'active' ? 'نشط' : 'غير نشط' }}
                                </span>
                            </p>
                            <p><strong>تاريخ الإنشاء:</strong> {{ $project->created_at->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    @if($project->description)
                        <div class="mt-3">
                            <strong>الوصف:</strong>
                            <p class="text-muted">{{ $project->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- إحصائيات -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        إحصائيات سريعة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="text-primary">{{ number_format($stats['total_logs']) }}</h3>
                        <p class="text-muted mb-0">إجمالي السجلات</p>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>سجلات اليوم:</span>
                        <span class="badge bg-success">{{ number_format($stats['today_logs']) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>سجلات الأسبوع:</span>
                        <span class="badge bg-info">{{ number_format($stats['week_logs']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- إدارة التوكنات -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        توكنات API
                    </h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#createTokenForm">
                        <i class="fas fa-plus me-1"></i>
                        إنشاء توكن جديد
                    </button>
                </div>
                <div class="card-body">
                    <!-- نموذج إنشاء توكن -->
                    <div class="collapse mb-4" id="createTokenForm">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-plus me-2"></i>
                                    إنشاء توكن API جديد
                                </h6>
                                <form action="{{ route('admin.projects.tokens.create', $project) }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="token_name" class="form-label">اسم التوكن</label>
                                                <input type="text" class="form-control @error('token_name') is-invalid @enderror"
                                                       id="token_name" name="token_name" value="{{ old('token_name') }}" required>
                                                @error('token_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="expires_at" class="form-label">تاريخ انتهاء الصلاحية (اختياري)</label>
                                                <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror"
                                                       id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                                                @error('expires_at')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-key me-1"></i>
                                            إنشاء التوكن
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#createTokenForm">
                                            إلغاء
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- قائمة التوكنات الموجودة -->
                    <div id="tokensContainer">
                        @php
                            $user = \App\Models\User::first();
                            $tokens = $user ? $user->tokens()->where('name', 'like', '% - ' . $project->name)->get() : collect();
                        @endphp

                        @if($tokens->count() > 0)
                            @foreach($tokens as $token)
                                <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                    <div>
                                        <strong>{{ $token->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            آخر استخدام: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'لم يستخدم بعد' }}
                                        </small>
                                        @if($token->expires_at)
                                            <br>
                                            <small class="text-warning">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                ينتهي: {{ $token->expires_at->format('Y-m-d H:i') }}
                                            </small>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="badge bg-success me-2">نشط</span>
                                        <form action="{{ route('admin.projects.tokens.revoke', $token) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('هل أنت متأكد من إلغاء هذا التوكن؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                                إلغاء
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-key fa-3x mb-3"></i>
                                <p>لا توجد توكنات متاحة</p>
                                <small>انقر على "إنشاء توكن جديد" لإنشاء أول توكن</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- إدارة IP Whitelist -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        قائمة IP المسموحة
                    </h5>
                    <a href="{{ route('admin.projects.ip-whitelist.index', $project) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-cog me-1"></i>
                        إدارة عناوين IP
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $ipWhitelistCount = $project->ipWhitelist()->count();
                        $activeIpCount = $project->ipWhitelist()->active()->count();
                        $recentIps = $project->ipWhitelist()->latest()->limit(3)->get();
                    @endphp

                    @if($ipWhitelistCount > 0)
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4 class="text-primary">{{ $ipWhitelistCount }}</h4>
                                    <small class="text-muted">إجمالي العناوين</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4 class="text-success">{{ $activeIpCount }}</h4>
                                    <small class="text-muted">عناوين نشطة</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4 class="text-warning">{{ $ipWhitelistCount - $activeIpCount }}</h4>
                                    <small class="text-muted">عناوين معطلة</small>
                                </div>
                            </div>
                        </div>

                        @if($recentIps->count() > 0)
                            <h6 class="mb-3">آخر العناوين المضافة:</h6>
                            @foreach($recentIps as $ip)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <code class="me-2">{{ $ip->ip_address }}</code>
                                        <span class="badge {{ $ip->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $ip->status === 'active' ? 'نشط' : 'معطل' }}
                                        </span>
                                        @if($ip->description)
                                            <br>
                                            <small class="text-muted">{{ $ip->description }}</small>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        {{ $ip->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            @endforeach
                        @endif

                        <div class="mt-3">
                            <a href="{{ route('admin.projects.ip-whitelist.create', $project) }}" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-plus me-1"></i>
                                إضافة IP جديد
                            </a>
                            <a href="{{ route('admin.projects.ip-whitelist.index', $project) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-list me-1"></i>
                                عرض جميع العناوين
                            </a>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shield-alt fa-3x mb-3"></i>
                            <p>لا توجد عناوين IP مسموحة</p>
                            <small>أضف عناوين IP موثوقة للتحكم في الوصول لـ API هذا المشروع</small>
                            <div class="mt-3">
                                <a href="{{ route('admin.projects.ip-whitelist.create', $project) }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    إضافة أول عنوان IP
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- آخر السجلات -->
    @if($stats['latest_log'])
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>
                            آخر سجل
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>الحدث:</strong>
                                <span class="badge bg-info">{{ $stats['latest_log']->event }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>التاريخ:</strong>
                                {{ $stats['latest_log']->occurred_at->format('Y-m-d H:i') }}
                            </div>
                            <div class="col-md-3">
                                <strong>IP:</strong>
                                <code>{{ $stats['latest_log']->ip_address }}</code>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.logs.show', $stats['latest_log']) }}" class="btn btn-outline-primary btn-sm">
                                    عرض التفاصيل
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard() {
    const tokenInput = document.getElementById('newTokenValue');
    tokenInput.select();
    document.execCommand('copy');

    // تغيير النص مؤقتاً
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(() => {
        button.innerHTML = originalHTML;
    }, 2000);
}

// إظهار نموذج إنشاء التوكن إذا كان هناك أخطاء validation
@if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        const collapse = new bootstrap.Collapse(document.getElementById('createTokenForm'), {
            show: true
        });
    });
@endif
</script>
@endpush
