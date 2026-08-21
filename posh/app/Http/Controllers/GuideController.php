<?php

namespace App\Http\Controllers;

use App\Support\PoshGuide;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $roleKey = PoshGuide::primaryRoleKey($user);
        $roleTabs = PoshGuide::roleTabs($user);

        $activeTab = $request->query('role', $roleKey);
        if (! array_key_exists($activeTab, $roleTabs)) {
            $activeTab = $roleKey;
        }

        $sections = PoshGuide::sectionsForTab($activeTab, $user);
        $glossary = PoshGuide::glossary();
        $timelines = PoshGuide::statutoryTimelines();

        return view('guide.index', [
            'sections' => $sections,
            'roleTabs' => $roleTabs,
            'activeTab' => $activeTab,
            'userRoleKey' => $roleKey,
            'glossary' => $glossary,
            'timelines' => $timelines,
        ]);
    }
}
