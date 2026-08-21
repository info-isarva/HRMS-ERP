<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AIController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Get AI insights dashboard
     */
    public function dashboard()
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $insights = $this->aiService->getInsights();

        return view('admin.ai.dashboard', compact('insights'));
    }

    /**
     * Get AI insights API
     */
    public function insights()
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $insights = $this->aiService->getInsights();

        return response()->json($insights);
    }

    /**
     * Clear AI cache
     */
    public function clearCache()
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Cache::forget('ai_insights_' . date('Y-m-d'));

        return response()->json(['message' => 'AI cache cleared successfully']);
    }
}