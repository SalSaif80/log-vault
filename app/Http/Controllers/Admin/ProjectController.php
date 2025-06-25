<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class ProjectController extends Controller
{
    /**
     * عرض قائمة المشاريع
     */
    public function index()
    {
        $projects = Project::orderBy('created_at', 'desc')->get();

        return view('admin.projects.index', compact('projects'));
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:projects',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $project = Project::create($validated);

        return redirect()->route('admin.projects.index')
            ->with('success', 'تم إنشاء المشروع بنجاح');
    }

    /**
     * عرض تفاصيل مشروع
     */
    public function show(Project $project)
    {
        // إحصائيات بسيطة بناء على source_system
        $stats = $project->stats;

        return view('admin.projects.show', compact('project', 'stats'));
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
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:projects,name,' . $project->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $project->update($validated);

        return redirect()->route('admin.projects.index')
            ->with('success', 'تم تحديث المشروع بنجاح');
    }

    /**
     * حذف مشروع
     */
    public function destroy(Project $project)
    {
        // التحقق من وجود سجلات مرتبطة بهذا المشروع
        $logsCount = \App\Models\Log::where('source_system', $project->slug)->count();

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
    public function createToken(Request $request, Project $project)
    {
        $validated = $request->validate([
            'token_name' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // إنشاء توكن Sanctum لأول admin user
        $user = \App\Models\User::first();
        if (!$user) {
            return redirect()->back()->with('error', 'لم يتم العثور على مستخدم إداري');
        }

        $tokenName = $validated['token_name'] . ' - ' . $project->name;

        $token = $user->createToken($tokenName)->plainTextToken;

        return redirect()->back()->with([
            'success' => 'تم إنشاء التوكن بنجاح',
            'new_token' => $token
        ]);
    }

    /**
     * الحصول على توكنات المشروع
     */
    public function tokens(Project $project)
    {
        $user = \App\Models\User::first();
        if (!$user) {
            return response()->json(['error' => 'No admin user found'], 404);
        }

        $tokens = $user->tokens()
            ->where('name', 'like', '% - ' . $project->name)
            ->get();

        return response()->json([
            'success' => true,
            'tokens' => $tokens
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
