<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Log;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'total_tokens' => PersonalAccessToken::count(),
            'total_logs' => Log::count(),
            'today_logs' => Log::whereDate('created_at', today())->count(),
            'week_logs' => Log::where('created_at', '>=', now()->subWeek())->count(),
            'source_systems' => Log::distinct()->count('source_system'),
        ];

        $recent_projects = Project::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recent_logs = Log::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_projects', 'recent_logs'));
    }
}
