<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateTokenProjectRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;
use App\Models\User;

class ProjectController extends Controller
{
    /**
     * عرض قائمة المشاريع
     */
    public function index(Request $request)
    {
        $query = Project::withCount('logs')->orderBy('created_at', 'desc');

        // فلترة بحالة المشروع
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // بحث في اسم المشروع
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $projects = $query->paginate(10)->withQueryString();

        // إحصائيات المشاريع
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'inactive_projects' => Project::where('status', 'inactive')->count(),
            'total_logs' => \App\Models\Log::count(),
        ];

        return view('admin.projects.index', compact('projects', 'stats'));
    }

    /**
     * عرض نموذج إنشاء مشروع جديد
     */
    public function create()
    {
        return view('admin.projects.create');
    }

    /**
     * حفظ مشروع جديد
     */
    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());

        return redirect()->route('admin.projects.index')
            ->with('success', 'تم إنشاء المشروع بنجاح');
    }

    /**
     * عرض تفاصيل مشروع
     */
    public function show(Project $project, Request $request)
    {
        // إحصائيات بسيطة
        $stats = $project->stats;

        // جلب السجلات المرتبطة بهذا المشروع مع الفلترة
        $logsQuery = $project->logs()->with('project')->orderBy('occurred_at', 'desc');

        // تطبيق فلاتر السجلات إذا كانت موجودة
        if ($request->filled('event')) {
            $logsQuery->where('event', $request->event);
        }

        if ($request->filled('start_date')) {
            $logsQuery->where('occurred_at', '>=', Carbon::parse($request->start_date));
        }

        if ($request->filled('end_date')) {
            $logsQuery->where('occurred_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $logsQuery->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%")
                  ->orWhere('causer_type', 'like', "%{$search}%");
            });
        }

        $logs = $logsQuery->paginate(20)->withQueryString();

        return view('admin.projects.show', compact('project', 'stats', 'logs', 'logsQuery'));
    }

    /**
     * عرض نموذج تعديل مشروع
     */
    public function edit(Project $project)
    {
        return view('admin.projects.edit', compact('project'));
    }

    /**
     * تحديث مشروع
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return redirect()->route('admin.projects.index')
            ->with('success', 'تم تحديث المشروع بنجاح');
    }

    /**
     * حذف مشروع
     */
    public function destroy(Project $project)
    {
        // التحقق من وجود سجلات مرتبطة بهذا المشروع
        $logsCount = $project->logs()->count();
        
        if ($logsCount > 0) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'لا يمكن حذف المشروع لأنه يحتوي على ' . $logsCount . ' سجل');
        }

        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'تم حذف المشروع بنجاح');
    }

    /**
     * إنشاء توكن API للمشروع
     */
    public function createToken(CreateTokenProjectRequest $request, Project $project)
    {

        // إنشاء توكن Sanctum لأول admin user
        $user = User::first();
        if (!$user) {
            return redirect()->back()->with('error', 'لم يتم العثور على مستخدم إداري');
        }

        $tokenName = $request->validated('token_name') . ' - ' . $project->name;

        $token = $user->createToken($tokenName, [$project->slug])->plainTextToken;

        return redirect()->back()->with([
            'success' => 'تم إنشاء التوكن بنجاح',
            'new_token' => $token
        ]);
    }
    /**
     * إلغاء (حذف) توكن API
     */
    public function revokeToken(PersonalAccessToken $token)
    {
        $token->delete();

        return redirect()->back()->with('success', 'تم إلغاء التوكن بنجاح');
    }
}
