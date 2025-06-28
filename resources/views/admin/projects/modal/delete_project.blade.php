<button class="btn btn-outline-danger btn-sm rounded-0" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $project->id }}">
    <i class="fas fa-trash"></i>
    حذف
</button>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal{{ $project->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $project->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel{{ $project->id }}">تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف المشروع <strong>{{ $project->name }}</strong>؟</p>
                <p class="text-danger"><small>هذا الإجراء لا يمكن التراجع عنه.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                        تأكيد الحذف
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>