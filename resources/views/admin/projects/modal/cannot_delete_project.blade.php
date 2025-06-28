<button class=" btn btn-outline-danger btn-sm rounded-0" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $project->id }}">
    <i class="fas fa-info-circle"></i>
    حذف
</button>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal{{ $project->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $project->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel{{ $project->id }}">لا يمكن حذف المشروع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>لا يمكن حذف المشروع <strong>{{ $project->name }}</strong> لأنه يحتوي على {{ $project->logs()->count() }} سجل.</p>
                <p>يجب حذف جميع السجلات أولاً قبل حذف المشروع.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <a href="{{ route('admin.logs.index', ['project_id' => $project->id]) }}" class="btn btn-primary">
                    <i class="fas fa-list-alt"></i>
                    عرض السجلات
                </a>
            </div>
        </div>
    </div>
</div>
