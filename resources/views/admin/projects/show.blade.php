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


        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('new_token'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h5><i class="fas fa-key me-2"></i>تم إنشاء التوكن بنجاح!</h5>
                <p><strong>مهم:</strong> احفظ هذا التوكن الآن! لن تتمكن من رؤيته مرة أخرى.</p>
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ session('new_token') }}" readonly
                        id="newTokenValue">
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
                        @if ($project->description)
                            <div class="mt-3">
                                <strong>الوصف:</strong>
                                <p class="text-muted">{{ $project->description }}</p>
                            </div>
                        @endif
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
                                                    <input type="text"
                                                        class="form-control @error('token_name') is-invalid @enderror"
                                                        id="token_name" name="token_name" value="{{ old('token_name') }}"
                                                        required>
                                                    @error('token_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="expires_at" class="form-label">تاريخ انتهاء الصلاحية
                                                        (اختياري)</label>
                                                    <input type="datetime-local"
                                                        class="form-control @error('expires_at') is-invalid @enderror"
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
                                            <button type="button" class="btn btn-secondary" data-bs-toggle="collapse"
                                                data-bs-target="#createTokenForm">
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
                                $tokens = $user
                                    ? $user
                                        ->tokens()
                                        ->where('name', 'like', '% - ' . $project->name)
                                        ->get()
                                    : collect();
                            @endphp

                            @if ($tokens->count() > 0)
                                @foreach ($tokens as $token)
                                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                        <div>
                                            <strong>{{ $token->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                آخر استخدام:
                                                {{ $token->last_used_at ? $token->last_used_at->diffForHumans(['locale' => 'ar']) : 'لم يستخدم بعد' }}
                                            </small>
                                            @if ($token->expires_at)
                                                <br>
                                                <small class="text-warning">
                                                    <i class="fas fa-hourglass-half me-1"></i>
                                                    ينتهي: {{ $token->expires_at->format('Y-m-d H:i') }}
                                                </small>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="badge bg-success me-2">نشط</span>
                                            <form action="{{ route('admin.projects.tokens.revoke', $token) }}"
                                                method="POST" class="d-inline"
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

        <!-- سجلات المشروع -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list-alt me-2"></i>
                            سجلات المشروع ({{ $logs->total() }} سجل)
                        </h5>
                        <div>
                            <a href="{{ route('admin.logs.index', ['project_id' => $project->id]) }}"
                                class="btn btn-primary btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i>
                                عرض جميع السجلات
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- فلاتر السجلات -->
                        <div class="mb-4">
                            <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#filtersCollapse" aria-expanded="false" aria-controls="filtersCollapse">
                                <i class="fas fa-filter me-1"></i>
                                فلاتر السجلات
                                <i class="fas fa-chevron-down ms-1"></i>
                            </button>

                            <div class="collapse mt-3" id="filtersCollapse">
                                <div class="card card-body">
                                    <form method="GET" action="{{ route('admin.projects.show', $project) }}">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label for="event" class="form-label">نوع الحدث</label>
                                                <select name="event" id="event" class="form-select">
                                                    <option value="">جميع الأحداث</option>
                                                    <option value="created"
                                                        {{ request('event') == 'created' ? 'selected' : '' }}>إنشاء
                                                    </option>
                                                    <option value="updated"
                                                        {{ request('event') == 'updated' ? 'selected' : '' }}>تحديث
                                                    </option>
                                                    <option value="deleted"
                                                        {{ request('event') == 'deleted' ? 'selected' : '' }}>حذف</option>
                                                    <option value="login"
                                                        {{ request('event') == 'login' ? 'selected' : '' }}>تسجيل دخول
                                                    </option>
                                                    <option value="logout"
                                                        {{ request('event') == 'logout' ? 'selected' : '' }}>تسجيل خروج
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="start_date" class="form-label">من تاريخ</label>
                                                <input type="date" name="start_date" id="start_date"
                                                    class="form-control" value="{{ request('start_date') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label for="end_date" class="form-label">إلى تاريخ</label>
                                                <input type="date" name="end_date" id="end_date"
                                                    class="form-control" value="{{ request('end_date') }}">
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary me-2">
                                                    <i class="fas fa-search me-1"></i>
                                                    تطبيق
                                                </button>
                                                <a href="{{ route('admin.projects.show', $project) }}"
                                                    class="btn btn-outline-secondary">
                                                    <i class="fas fa-times me-1"></i>
                                                    إلغاء
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @if ($logs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="15%">رقم السجل</th>
                                            <th width="35%">تفاصيل الحدث</th>
                                            <th width="20%">العنصر المتأثر</th>
                                            <th width="15%">التاريخ والوقت</th>
                                            <th width="15%">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($logs as $log)
                                            <tr>
                                                <td>
                                                    <span
                                                        class="badge bg-dark fs-6">#{{ $log->external_log_id ?? $log->id }}</span>
                                                </td>
                                                <td>
                                                    <div>
                                                        @if ($log->event)
                                                            @php
                                                                $eventColors = [
                                                                    'created' => 'bg-success',
                                                                    'updated' => 'bg-warning text-dark',
                                                                    'deleted' => 'bg-danger',
                                                                    'login' => 'bg-info',
                                                                    'logout' => 'bg-secondary',
                                                                ];
                                                                $eventNames = [
                                                                    'created' => 'إنشاء',
                                                                    'updated' => 'تحديث',
                                                                    'deleted' => 'حذف',
                                                                    'login' => 'دخول',
                                                                    'logout' => 'خروج',
                                                                ];
                                                            @endphp
                                                            <span
                                                                class="badge {{ $eventColors[$log->event] ?? 'bg-secondary' }} mb-1">
                                                                {{ $eventNames[$log->event] ?? $log->event }}
                                                            </span>
                                                        @endif
                                                        <div class="mt-1">
                                                            <small>{{ $log->description }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($log->subject_type)
                                                        @php
                                                            $typeNames = [
                                                                'App\Models\User' => 'مستخدم',
                                                                'App\Models\Course' => 'كورس',
                                                                'App\Models\Enrollment' => 'تسجيل',
                                                            ];
                                                        @endphp
                                                        <div>
                                                            <span
                                                                class="badge bg-light text-dark">{{ $typeNames[$log->subject_type] ?? class_basename($log->subject_type) }}</span>
                                                            @if ($log->subject_id)
                                                                <br><small class="text-muted">ID:
                                                                    {{ $log->subject_id }}</small>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $log->occurred_at ? $log->occurred_at->format('Y-m-d') : $log->created_at->format('Y-m-d') }}</strong>
                                                        <br>
                                                        <small
                                                            class="text-muted">{{ $log->occurred_at ? $log->occurred_at->format('H:i:s') : $log->created_at->format('H:i:s') }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.logs.show', $log) }}"
                                                        class="btn btn-outline-primary btn-sm" title="عرض التفاصيل">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $logs->links() }}
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">لا توجد سجلات</h5>
                                <p class="text-muted">لم يتم العثور على سجلات لهذا المشروع تطابق معايير البحث المحددة.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- آخر السجلات -->
        @if (isset($stats['latest_log']) && $stats['latest_log'])
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
                                    <span class="badge bg-info">{{ $stats['latest_log']->event ?? 'غير محدد' }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>التاريخ:</strong>
                                    @if ($stats['latest_log']->occurred_at)
                                        {{ $stats['latest_log']->occurred_at->format('Y-m-d H:i') }}
                                    @elseif($stats['latest_log']->created_at)
                                        {{ $stats['latest_log']->created_at->format('Y-m-d H:i') }}
                                    @else
                                        غير محدد
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <strong>المستخدم:</strong>
                                    {{ $stats['latest_log']->causer_id ?? 'النظام' }}
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('admin.logs.show', $stats['latest_log']) }}"
                                        class="btn btn-outline-primary btn-sm">
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
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                const collapse = new bootstrap.Collapse(document.getElementById('createTokenForm'), {
                    show: true
                });
            });
        @endif
    </script>
@endpush
