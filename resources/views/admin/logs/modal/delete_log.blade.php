<button type="button" class="btn btn-outline-danger rounded-0" data-bs-toggle="modal"
    data-bs-target="#deleteModal{{ $log->id }}" title="حذف">
    <i class="fas fa-trash"></i>
</button>

<!-- Modal حذف السجل -->
<div class="modal fade" id="deleteModal{{ $log->id }}" tabindex="-1"
    aria-labelledby="deleteModalLabel{{ $log->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel{{ $log->id }}">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    تأكيد حذف السجل
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">هل أنت متأكد من حذف هذا السجل؟</p>
                <p class="text-muted small mt-2">
                    <strong>الحدث:</strong> {{ $log->event }}<br>
                    <strong>النظام المصدر:</strong> {{ $log->source_system }}<br>
                    <strong>التاريخ:</strong>
                    {{ $log->occurred_at ? $log->occurred_at->format('Y-m-d H:i:s') : $log->created_at->format('Y-m-d H:i:s') }}
                </p>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>تحذير:</strong> لا يمكن التراجع عن هذا الإجراء بعد الحذف.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-0" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    إلغاء
                </button>
                <form action="{{ route('admin.logs.destroy', $log) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-0">
                        <i class="fas fa-trash me-1"></i>
                        تأكيد الحذف
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
