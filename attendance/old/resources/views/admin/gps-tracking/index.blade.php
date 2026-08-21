@extends('layouts.app')

@section('title', 'GPS Field Tracking - HRMS')
@section('page-title', 'GPS Field Tracking')

@section('content')
<div class="p-4 lg:p-6" id="gps-tracking-app"
     data-tracking-url="{{ route('admin.gps-tracking.data') }}"
     data-initial-employee="{{ $selectedEmployeeId }}"
     data-initial-date="{{ $selectedDate }}"
     data-timezone="{{ config('app.timezone', 'Asia/Kolkata') }}">

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        {{-- Toolbar --}}
        <div class="px-4 py-2.5 lg:px-5 border-b border-slate-100 bg-slate-50/60">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2.5">
                <h1 class="text-base font-semibold text-slate-900">Field GPS Tracking</h1>
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="relative min-w-[200px]">
                        <i class="fas fa-user absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <select id="employee-select"
                                class="w-full pl-8 pr-3 py-2 text-xs rounded-lg border border-slate-200 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" @selected($employee->id == $selectedEmployeeId)>
                                    {{ $employee->name }} ({{ $employee->employee_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <i class="fas fa-calendar absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="date" id="track-date" value="{{ $selectedDate }}"
                               class="pl-8 pr-3 py-2 text-xs rounded-lg border border-slate-200 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <button id="load-tracking"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 text-white text-xs font-medium hover:bg-blue-700 transition-colors">
                        <i class="fas fa-route text-[10px]"></i>
                        Load
                    </button>
                </div>
            </div>
        </div>

        <div class="gps-split-layout grid grid-cols-1 xl:grid-cols-5 gap-4 p-4 bg-slate-100/70 xl:items-stretch">
            {{-- Map panel --}}
            <div class="gps-panel gps-panel-map xl:col-span-3 flex flex-col xl:min-h-[calc(100vh-220px)]">
                <div class="gps-panel-label flex items-center justify-between gap-2">
                    <span><i class="fas fa-map text-blue-500"></i> Route map</span>
                    <span id="route-matched-badge" class="hidden text-[10px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                        <i class="fas fa-road text-[9px] mr-1"></i> Road-aligned
                    </span>
                </div>
                <div class="relative flex-1 min-h-0 flex flex-col">
                    <div id="gps-map" class="flex-1 min-h-[280px] sm:min-h-[340px] w-full bg-slate-100"></div>
                    <div class="absolute top-3 right-3 flex flex-col gap-2 z-10 gps-map-controls">
                        <button type="button" id="map-zoom-in" class="map-ctrl-btn" title="Zoom in"><i class="fas fa-plus text-xs"></i></button>
                        <button type="button" id="map-zoom-out" class="map-ctrl-btn" title="Zoom out"><i class="fas fa-minus text-xs"></i></button>
                        <button type="button" id="map-recenter" class="map-ctrl-btn" title="Fit route"><i class="fas fa-crosshairs text-xs"></i></button>
                    </div>
                </div>

                {{-- Playback sits below map, never overlays it --}}
                <div id="playback-bar" class="playback-panel hidden">
                    <div class="playback-toolbar">
                        <button type="button" id="playback-toggle" class="playback-play-btn" aria-label="Play route">
                            <i class="fas fa-play text-xs ml-0.5" id="playback-icon"></i>
                        </button>
                        <div class="playback-clock">
                            <div id="playback-time" class="playback-now tabular-nums">--:--</div>
                            <div class="playback-range">
                                <span id="playback-start">--:--</span>
                                <span class="playback-range-sep">→</span>
                                <span id="playback-end">--:--</span>
                            </div>
                        </div>
                        <div class="playback-speed-group" id="playback-speed-group" role="group" aria-label="Playback speed">
                            <button type="button" class="playback-speed-opt is-active" data-speed="0">1×</button>
                            <button type="button" class="playback-speed-opt" data-speed="1">2×</button>
                            <button type="button" class="playback-speed-opt" data-speed="2">4×</button>
                            <button type="button" class="playback-speed-opt" data-speed="3">8×</button>
                        </div>
                        <button type="button" id="playback-reset" class="playback-secondary-btn" title="Reset to start">
                            <i class="fas fa-rotate-left text-[10px]"></i>
                        </button>
                    </div>
                    <div class="playback-scrubber">
                        <div class="playback-rail" aria-hidden="true">
                            <div id="playback-rail-fill" class="playback-rail-fill"></div>
                            <div id="playback-markers" class="playback-markers"></div>
                        </div>
                        <input type="range" id="playback-slider" min="0" max="0" value="0" class="playback-slider w-full" aria-label="Route playback position">
                        <div class="playback-scrubber-meta">
                            <span id="playback-progress-label">0%</span>
                            <span class="text-slate-400">Drag or tap an event below</span>
                        </div>
                    </div>
                    <div class="playback-events-scroll">
                        <div id="playback-event-chips" class="playback-events-strip"></div>
                    </div>
                </div>
            </div>

            {{-- Timeline panel --}}
            <div class="gps-panel gps-panel-timeline xl:col-span-2 flex flex-col xl:min-h-[calc(100vh-220px)] min-h-0">
                <div class="gps-panel-label"><i class="fas fa-list-ul text-emerald-600"></i> Day timeline</div>
                <div id="employee-header" class="hidden border-b border-slate-100 bg-gradient-to-r from-rose-50/80 to-pink-50/80">
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div id="employee-avatar" class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-400 to-pink-500 text-white flex items-center justify-center font-semibold text-sm flex-shrink-0 shadow-sm"></div>
                        <div class="min-w-0 flex-1">
                            <div id="employee-name" class="font-semibold text-slate-900 text-sm truncate"></div>
                            <div id="employee-code" class="text-xs text-slate-500 truncate mt-0.5"></div>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <button type="button" id="date-prev" class="date-nav-btn"><i class="fas fa-chevron-left text-[10px]"></i></button>
                            <div id="display-date" class="text-xs font-semibold text-slate-800 min-w-[80px] text-center">—</div>
                            <button type="button" id="date-next" class="date-nav-btn"><i class="fas fa-chevron-right text-[10px]"></i></button>
                        </div>
                        <div id="playback-live-badge" class="hidden flex-shrink-0">
                            <span class="live-badge"><span class="live-dot"></span> Live</span>
                        </div>
                    </div>
                </div>

                <div id="summary-card" class="hidden grid grid-cols-3 gap-2 px-3 py-2.5 border-b border-slate-100 bg-slate-50/40">
                    <div class="summary-stat-compact">
                        <div class="summary-stat-icon-sm bg-amber-100 text-amber-600"><i class="far fa-clock"></i></div>
                        <div class="text-[10px] text-slate-500 font-medium uppercase tracking-wide">Travel</div>
                        <div id="summary-travel" class="text-xs font-bold text-slate-900 mt-0.5">-</div>
                    </div>
                    <div class="summary-stat-compact">
                        <div class="summary-stat-icon-sm bg-blue-100 text-blue-600"><i class="fas fa-road"></i></div>
                        <div class="text-[10px] text-slate-500 font-medium uppercase tracking-wide">Distance</div>
                        <div id="summary-distance" class="text-xs font-bold text-slate-900 mt-0.5">-</div>
                    </div>
                    <div class="summary-stat-compact">
                        <div class="summary-stat-icon-sm bg-emerald-100 text-emerald-600"><i class="fas fa-map-pin"></i></div>
                        <div class="text-[10px] text-slate-500 font-medium uppercase tracking-wide">Visits</div>
                        <div id="summary-visits" class="text-xs font-bold text-slate-900 mt-0.5">-</div>
                    </div>
                </div>

                <div id="timeline-scroll" class="flex-1 overflow-y-auto px-4 py-3 min-h-[160px] min-h-0">
                    <div id="timeline-empty" class="flex flex-col items-center justify-center text-center py-10 text-slate-400">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
                            <i class="fas fa-location-dot text-xl text-slate-300"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-500">No route loaded</p>
                        <p class="text-xs mt-1">Pick an employee and date, then load the route</p>
                    </div>
                    <div id="timeline-list" class="timeline-list space-y-0 hidden"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    #gps-tracking-app {
        position: relative;
        z-index: 0;
    }
    .gps-split-layout {
        border-top: 1px solid #e2e8f0;
    }
    .gps-panel {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 0.875rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06), 0 4px 12px rgba(15, 23, 42, 0.04);
        min-height: 0;
    }
    .gps-panel-map {
        border-color: #bfdbfe;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.06), 0 4px 14px rgba(15, 23, 42, 0.05);
    }
    #gps-map {
        position: relative;
        z-index: 0;
        isolation: isolate;
    }
    #gps-tracking-app .leaflet-pane { z-index: 1 !important; }
    #gps-tracking-app .leaflet-tile-pane { z-index: 2 !important; }
    #gps-tracking-app .leaflet-overlay-pane { z-index: 3 !important; }
    #gps-tracking-app .leaflet-shadow-pane { z-index: 4 !important; }
    #gps-tracking-app .leaflet-marker-pane { z-index: 5 !important; }
    #gps-tracking-app .leaflet-tooltip-pane { z-index: 6 !important; }
    #gps-tracking-app .leaflet-popup-pane { z-index: 7 !important; }
    #gps-tracking-app .leaflet-control-container { z-index: 8 !important; }
    .gps-panel-timeline {
        border-color: #bbf7d0;
        box-shadow: 0 1px 3px rgba(16, 185, 129, 0.06), 0 4px 14px rgba(15, 23, 42, 0.05);
    }
    .gps-panel-label {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 0.875rem;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        background: linear-gradient(to bottom, #f8fafc, #fff);
        border-bottom: 1px solid #e2e8f0;
    }
    .gps-panel-label i { font-size: 0.6rem; }

    .map-ctrl-btn {
        width: 2.25rem; height: 2.25rem; border-radius: 0.625rem;
        background: rgba(255,255,255,0.96); border: 1px solid #e2e8f0;
        color: #475569; box-shadow: 0 2px 8px rgba(15,23,42,0.1);
        display: flex; align-items: center; justify-content: center;
        transition: all 0.15s ease;
    }
    .map-ctrl-btn:hover { background: #fff; color: #2563eb; }

    .playback-panel {
        flex-shrink: 0;
        border-top: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
        padding: 0.75rem 0.875rem 0.875rem;
    }
    .playback-toolbar {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin-bottom: 0.625rem;
    }
    .playback-clock {
        flex: 1;
        min-width: 0;
    }
    .playback-now {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1d4ed8;
        line-height: 1.1;
    }
    .playback-range {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.15rem;
        font-size: 0.62rem;
        color: #94a3b8;
        font-variant-numeric: tabular-nums;
    }
    .playback-range-sep { color: #cbd5e1; font-size: 0.55rem; }

    .playback-speed-group {
        display: inline-flex;
        padding: 2px;
        border-radius: 0.5rem;
        background: #e2e8f0;
        border: 1px solid #cbd5e1;
        flex-shrink: 0;
    }
    .playback-speed-opt {
        min-width: 1.85rem;
        padding: 0.28rem 0.4rem;
        border: none;
        border-radius: 0.375rem;
        background: transparent;
        color: #64748b;
        font-size: 0.62rem;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .playback-speed-opt:hover { color: #334155; }
    .playback-speed-opt.is-active {
        background: #fff;
        color: #2563eb;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }

    .playback-scrubber { position: relative; padding: 0.15rem 0; }
    .playback-rail {
        position: absolute;
        left: 8px;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        height: 6px;
        border-radius: 9999px;
        background: #e2e8f0;
        overflow: visible;
        pointer-events: none;
        z-index: 0;
    }
    .playback-rail-fill {
        height: 100%;
        width: 0%;
        border-radius: 9999px;
        background: linear-gradient(90deg, #60a5fa, #2563eb);
        transition: width 0.12s ease;
    }
    .playback-scrubber-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.35rem;
        font-size: 0.58rem;
        color: #64748b;
    }
    #playback-progress-label { font-weight: 700; color: #475569; }

    .playback-events-scroll {
        margin-top: 0.625rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .playback-events-scroll::-webkit-scrollbar { height: 4px; }
    .playback-events-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    .playback-events-strip {
        display: flex;
        gap: 0.5rem;
        min-width: min-content;
        padding-bottom: 2px;
    }

    .playback-play-btn {
        width: 2.25rem; height: 2.25rem; flex-shrink: 0; border-radius: 9999px;
        background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 3px 10px rgba(37,99,235,0.35);
        transition: transform 0.15s ease;
    }
    .playback-play-btn:hover { transform: scale(1.04); }
    .playback-play-btn.is-playing { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    .playback-secondary-btn {
        width: 2rem; height: 2rem; flex-shrink: 0; border-radius: 0.5rem;
        background: #fff; border: 1px solid #e2e8f0; color: #64748b;
        display: flex; align-items: center; justify-content: center;
    }
    .playback-secondary-btn:hover { background: #f8fafc; color: #334155; }

    .playback-markers {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }
    .playback-event-marker {
        position: absolute;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.22);
        pointer-events: auto;
        cursor: pointer;
        z-index: 3;
        transition: transform 0.12s ease;
    }
    .playback-event-marker:hover { transform: translate(-50%, -50%) scale(1.15); }

    .playback-event-card {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        flex-shrink: 0;
        min-width: 7.5rem;
        max-width: 10rem;
        padding: 0.4rem 0.55rem;
        border-radius: 0.625rem;
        border: 2px solid #e2e8f0;
        background: #fff;
        text-align: left;
        cursor: pointer;
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }
    .playback-event-card:hover {
        border-color: #cbd5e1;
    }
    .playback-event-card.is-active {
        border-width: 2px;
        animation: playback-card-vibrate 0.55s ease-in-out infinite;
    }
    .playback-card-office-in.is-active { border-color: #2563eb; }
    .playback-card-office-out.is-active { border-color: #6366f1; }
    .playback-card-visit-in.is-active { border-color: #059669; }
    .playback-card-visit-out.is-active { border-color: #ea580c; }
    .playback-card-travel.is-active { border-color: #d97706; }
    @keyframes playback-card-vibrate {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-1.5px); }
        50% { transform: translateX(1.5px); }
        75% { transform: translateX(-1px); }
    }
    .playback-event-icon {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 0.4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #fff;
        font-size: 0.55rem;
    }
    .playback-event-body { min-width: 0; }
    .playback-event-time {
        font-size: 0.62rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }
    .playback-event-label {
        font-size: 0.58rem;
        color: #64748b;
        line-height: 1.2;
        margin-top: 0.1rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .playback-card-office-in .playback-event-icon { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .playback-card-office-out .playback-event-icon { background: linear-gradient(135deg, #818cf8, #6366f1); }
    .playback-card-visit-in .playback-event-icon { background: linear-gradient(135deg, #10b981, #059669); }
    .playback-card-visit-out .playback-event-icon { background: linear-gradient(135deg, #fb923c, #f97316); }
    .playback-card-travel .playback-event-icon { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
    .playback-card-office-in { border-color: #bfdbfe; background: #f8fbff; }
    .playback-card-office-out { border-color: #c7d2fe; background: #f8f9ff; }
    .playback-card-visit-in { border-color: #a7f3d0; background: #f6fffb; }
    .playback-card-visit-out { border-color: #fed7aa; background: #fffaf5; }
    .playback-card-travel { border-color: #fde68a; background: #fffdf5; }

    .playback-slider {
        position: relative;
        z-index: 2;
        -webkit-appearance: none; appearance: none; height: 28px;
        background: transparent;
        outline: none; cursor: pointer;
    }
    .playback-slider::-webkit-slider-runnable-track {
        height: 6px;
        background: transparent;
    }
    .playback-slider::-moz-range-track {
        height: 6px;
        background: transparent;
        border: none;
    }
    .playback-slider::-webkit-slider-thumb {
        -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%;
        background: #fff; border: 2.5px solid #2563eb; box-shadow: 0 1px 6px rgba(37,99,235,0.3); cursor: grab;
    }
    .playback-slider::-moz-range-thumb {
        width: 16px; height: 16px; border-radius: 50%;
        background: #fff; border: 2.5px solid #2563eb; cursor: grab;
    }

    .date-nav-btn {
        width: 1.875rem; height: 1.875rem; border-radius: 0.5rem; border: 1px solid #e2e8f0;
        color: #64748b; display: flex; align-items: center; justify-content: center;
    }
    .date-nav-btn:hover { background: #f8fafc; color: #2563eb; border-color: #bfdbfe; }

    .summary-stat-compact {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 0.625rem;
        padding: 0.5rem 0.4rem; text-align: center;
    }
    .summary-stat-icon-sm {
        width: 1.5rem; height: 1.5rem; border-radius: 0.4rem;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.6rem; margin-bottom: 0.2rem;
    }

    .live-badge {
        display: inline-flex; align-items: center; gap: 0.35rem;
        font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;
        color: #dc2626; background: #fef2f2; border: 1px solid #fecaca;
        padding: 0.2rem 0.5rem; border-radius: 9999px;
    }
    .live-dot {
        width: 6px; height: 6px; border-radius: 50%; background: #ef4444;
        animation: live-pulse 1.2s ease-in-out infinite;
    }
    @keyframes live-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.85); }
    }

    /* Connector lines live only in the gap below each icon */
    .timeline-list {
        position: relative;
    }

    .timeline-item {
        position: relative;
        display: flex;
        align-items: stretch;
        gap: 0.875rem;
        padding-bottom: 1.25rem;
        transition: opacity 0.25s ease;
    }
    .timeline-item:last-child { padding-bottom: 0; }

    .timeline-rail {
        flex-shrink: 0;
        width: 2.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .timeline-icon {
        position: relative;
        flex-shrink: 0;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        border: 3px solid #fff;
        transition: box-shadow 0.25s ease, transform 0.25s ease;
    }
    .timeline-segment {
        flex: 1;
        width: 2px;
        min-height: 1.25rem;
        margin-top: 6px;
        position: relative;
        background: #d1d5db;
        border-radius: 999px;
        overflow: hidden;
    }
    .timeline-item:last-child .timeline-segment { display: none; }
    .timeline-segment-fill {
        display: block;
        width: 100%;
        height: 0;
        background: linear-gradient(180deg, #2563eb, #60a5fa);
        border-radius: 999px;
        transition: height 0.35s ease;
    }
    .timeline-item.timeline-done .timeline-segment-fill { height: 100%; }
    .timeline-item.timeline-active .timeline-icon {
        animation: timeline-icon-vibrate 0.55s ease-in-out infinite;
        box-shadow: 0 0 0 2px rgba(59,130,246,0.18), 0 2px 8px rgba(37,99,235,0.2);
    }
    .timeline-item.timeline-active.timeline-type-visit .timeline-icon {
        box-shadow: 0 0 0 2px rgba(16,185,129,0.18), 0 2px 8px rgba(5,150,105,0.2);
    }
    .timeline-item.timeline-active.timeline-type-travel .timeline-icon {
        box-shadow: 0 0 0 2px rgba(245,158,11,0.22), 0 2px 8px rgba(245,158,11,0.25);
    }
    .timeline-item.timeline-active .timeline-icon::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 0.75rem;
        border: 2px solid rgba(59,130,246,0.45);
        animation: icon-ring 1.5s ease-out infinite;
        pointer-events: none;
    }
    .timeline-item.timeline-active.timeline-type-visit .timeline-icon::after {
        border-color: rgba(16,185,129,0.45);
    }
    .timeline-item.timeline-active.timeline-type-travel .timeline-icon::after {
        border-color: rgba(245,158,11,0.5);
    }
    @keyframes icon-ring {
        0% { opacity: 0.7; transform: scale(1); }
        100% { opacity: 0; transform: scale(1.15); }
    }
    @keyframes timeline-icon-vibrate {
        0%, 100% { transform: scale(1.06) translateX(0); }
        25% { transform: scale(1.06) translateX(-1.5px); }
        50% { transform: scale(1.06) translateX(1.5px); }
        75% { transform: scale(1.06) translateX(-1px); }
    }

    .timeline-item.timeline-past .timeline-card { opacity: 0.55; }
    .timeline-item.timeline-active { opacity: 1; }

    .timeline-card {
        flex: 1;
        min-width: 0;
        border-radius: 0.75rem;
        padding: 0.75rem 0.875rem;
        border: 2px solid transparent;
        transition: border-color 0.3s ease, box-shadow 0.3s ease, opacity 0.25s ease;
    }
    .timeline-item.timeline-active .timeline-card {
        opacity: 1 !important;
    }
    .timeline-item.timeline-active.timeline-type-travel .timeline-card {
        border-color: #f59e0b !important;
        animation: playback-card-vibrate 0.55s ease-in-out infinite, card-active-soft-amber 2.5s ease-in-out infinite;
    }
    .visit-card { background: linear-gradient(135deg, #ecfdf5, #f0fdf4); border-color: #bbf7d0; }
    .office-card { background: linear-gradient(135deg, #eff6ff, #f0f9ff); border-color: #bfdbfe; }
    .travel-card { background: linear-gradient(135deg, #fffbeb, #fef3c7); border-color: #fde68a; }

    /* Four distinct session card colors */
    .card-office-in {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border-color: #93c5fd;
    }
    .card-office-out {
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        border-color: #a5b4fc;
    }
    .card-visit-in {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border-color: #6ee7b7;
    }
    .card-visit-out {
        background: linear-gradient(135deg, #fff7ed, #ffedd5);
        border-color: #fdba74;
    }

    .timeline-item.timeline-active .card-office-in {
        background: linear-gradient(135deg, #dbeafe, #eff6ff);
        border-color: #3b82f6 !important;
        animation: playback-card-vibrate 0.55s ease-in-out infinite, card-active-soft-blue 2.5s ease-in-out infinite;
    }
    .timeline-item.timeline-active .card-office-out {
        background: linear-gradient(135deg, #e0e7ff, #eef2ff);
        border-color: #6366f1 !important;
        animation: playback-card-vibrate 0.55s ease-in-out infinite, card-active-soft-indigo 2.5s ease-in-out infinite;
    }
    .timeline-item.timeline-active .card-visit-in {
        background: linear-gradient(135deg, #d1fae5, #ecfdf5);
        border-color: #10b981 !important;
        animation: playback-card-vibrate 0.55s ease-in-out infinite, card-active-soft-green 2.5s ease-in-out infinite;
    }
    .timeline-item.timeline-active .card-visit-out {
        background: linear-gradient(135deg, #ffedd5, #fff7ed);
        border-color: #f97316 !important;
        animation: playback-card-vibrate 0.55s ease-in-out infinite, card-active-soft-orange 2.5s ease-in-out infinite;
    }

    @keyframes card-active-soft-blue {
        0%, 100% { box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08), 0 4px 14px rgba(37, 99, 235, 0.08); }
        50% { box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.14), 0 6px 20px rgba(37, 99, 235, 0.12); }
    }
    @keyframes card-active-soft-indigo {
        0%, 100% { box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08), 0 4px 14px rgba(79, 70, 229, 0.08); }
        50% { box-shadow: 0 0 0 5px rgba(99, 102, 241, 0.14), 0 6px 20px rgba(79, 70, 229, 0.12); }
    }
    @keyframes card-active-soft-green {
        0%, 100% { box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.08), 0 4px 14px rgba(5, 150, 105, 0.08); }
        50% { box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.14), 0 6px 20px rgba(5, 150, 105, 0.12); }
    }
    @keyframes card-active-soft-orange {
        0%, 100% { box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1), 0 4px 14px rgba(234, 88, 12, 0.08); }
        50% { box-shadow: 0 0 0 5px rgba(249, 115, 22, 0.16), 0 6px 20px rgba(234, 88, 12, 0.12); }
    }
    @keyframes card-active-soft-amber {
        0%, 100% { box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1), 0 4px 14px rgba(245, 158, 11, 0.08); }
        50% { box-shadow: 0 0 0 5px rgba(245, 158, 11, 0.16), 0 6px 20px rgba(245, 158, 11, 0.12); }
    }

    .timeline-item.timeline-active .visit-card { background: linear-gradient(135deg, #d1fae5, #ecfdf5); }
    .timeline-item.timeline-active .office-card { background: linear-gradient(135deg, #dbeafe, #eff6ff); }
    .timeline-item.timeline-active .travel-card { background: linear-gradient(135deg, #fef3c7, #fffbeb); }

    .timeline-item.timeline-active.timeline-variant-office-in .timeline-icon {
        box-shadow: 0 0 0 2px rgba(59,130,246,0.18), 0 2px 8px rgba(37,99,235,0.2);
    }
    .timeline-item.timeline-active.timeline-variant-office-out .timeline-icon {
        box-shadow: 0 0 0 2px rgba(99,102,241,0.18), 0 2px 8px rgba(79,70,229,0.2);
    }
    .timeline-item.timeline-active.timeline-variant-visit-in .timeline-icon {
        box-shadow: 0 0 0 2px rgba(16,185,129,0.18), 0 2px 8px rgba(5,150,105,0.2);
    }
    .timeline-item.timeline-active.timeline-variant-visit-out .timeline-icon {
        box-shadow: 0 0 0 2px rgba(249,115,22,0.2), 0 2px 8px rgba(234,88,12,0.22);
    }
    .timeline-item.timeline-active.timeline-variant-office-in .timeline-icon::after { border-color: rgba(59,130,246,0.45); }
    .timeline-item.timeline-active.timeline-variant-office-out .timeline-icon::after { border-color: rgba(99,102,241,0.45); }
    .timeline-item.timeline-active.timeline-variant-visit-in .timeline-icon::after { border-color: rgba(16,185,129,0.45); }
    .timeline-item.timeline-active.timeline-variant-visit-out .timeline-icon::after { border-color: rgba(249,115,22,0.5); }

    .tag-office-in { color: #1d4ed8; background: #dbeafe; }
    .tag-office-out { color: #4338ca; background: #e0e7ff; }
    .tag-visit-in { color: #047857; background: #d1fae5; }
    .tag-visit-out { color: #c2410c; background: #ffedd5; }
    .timeline-type-travel .active-now-tag {
        color: #d97706;
        background: #fde68a;
    }

    .active-now-tag {
        display: inline-flex; align-items: center; gap: 0.3rem;
        font-size: 0.65rem; font-weight: 600; color: #2563eb;
        background: #dbeafe; padding: 0.15rem 0.5rem; border-radius: 9999px; margin-top: 0.5rem;
    }

    .status-pill {
        display: inline-flex; align-items: center; gap: 0.35rem;
        font-size: 0.7rem; font-weight: 500; padding: 0.2rem 0.55rem; border-radius: 9999px;
    }
    .status-pill::before { content: ''; width: 5px; height: 5px; border-radius: 9999px; }
    .status-blue { background: #dbeafe; color: #1d4ed8; }
    .status-blue::before { background: #3b82f6; }
    .status-green { background: #d1fae5; color: #047857; }
    .status-green::before { background: #10b981; }
    .status-amber { background: #fef3c7; color: #b45309; }
    .status-amber::before { background: #f59e0b; }
    .status-office-in { background: #dbeafe; color: #1d4ed8; }
    .status-office-in::before { background: #3b82f6; }
    .status-office-out { background: #e0e7ff; color: #4338ca; }
    .status-office-out::before { background: #6366f1; }
    .status-visit-in { background: #d1fae5; color: #047857; }
    .status-visit-in::before { background: #10b981; }
    .status-visit-out { background: #ffedd5; color: #c2410c; }
    .status-visit-out::before { background: #f97316; }

    .gps-marker-icon { background: transparent; border: none; }
    .playback-pulse {
        width: 16px; height: 16px; border-radius: 50%;
        background: #2563eb; border: 3px solid #fff;
        box-shadow: 0 0 0 5px rgba(37,99,235,0.3);
        animation: marker-pulse 1s ease-in-out infinite;
    }
    @keyframes marker-pulse {
        0%, 100% { box-shadow: 0 0 0 4px rgba(37,99,235,0.3); }
        50% { box-shadow: 0 0 0 8px rgba(37,99,235,0.15); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('gps-tracking-app');
    const appTimezone = app.dataset.timezone || 'Asia/Kolkata';
    const trackingUrl = app.dataset.trackingUrl;
    const employeeSelect = document.getElementById('employee-select');
    const trackDate = document.getElementById('track-date');
    const loadBtn = document.getElementById('load-tracking');
    const playbackSlider = document.getElementById('playback-slider');
    const playbackToggle = document.getElementById('playback-toggle');
    const playbackIcon = document.getElementById('playback-icon');
    const playbackSpeedGroup = document.getElementById('playback-speed-group');
    const playbackRailFill = document.getElementById('playback-rail-fill');
    const timelineScroll = document.getElementById('timeline-scroll');

    const PLAYBACK_BASE_MS = 550;
    const PLAYBACK_SPEEDS = [1, 2, 4, 8];
    const PLAYBACK_SLIDER_STEPS = 1000;
    let map, routeLine, markerLayer, playbackMarker, playbackTimer = null;
    let routePoints = [], timelineMeta = [], playbackEvents = [], playing = false;
    let playbackSpeedIdx = 0;

    map = L.map('gps-map', { zoomControl: false }).setView([12.871, 74.848], 13);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        subdomains: 'abcd',
        maxZoom: 20,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
    }).addTo(map);
    markerLayer = L.layerGroup().addTo(map);

    function iconHtml(type) {
        const colors = { office: '#2563eb', visit: '#059669', travel: '#f59e0b' };
        const icons = { office: 'briefcase', visit: 'map-marker-alt', travel: 'truck' };
        const color = colors[type] || '#2563eb';
        const icon = icons[type] || 'map-marker-alt';
        return L.divIcon({
            className: 'gps-marker-icon',
            html: `<div style="background:${color};width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.18)"><i class="fas fa-${icon}" style="font-size:11px"></i></div>`,
            iconSize: [30, 30], iconAnchor: [15, 15],
        });
    }

    function playbackIconHtml() {
        return L.divIcon({
            className: 'gps-marker-icon',
            html: '<div class="playback-pulse"></div>',
            iconSize: [16, 16], iconAnchor: [8, 8],
        });
    }

    function parseCalendarDate(dateStr) {
        const [y, m, d] = dateStr.split('-').map(Number);
        return new Date(y, m - 1, d);
    }

    function formatDateInput(d) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function formatDisplayDate(v) {
        const d = parseCalendarDate(v);
        return d.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    }

    function formatTime(iso) {
        if (!iso) return '--:--';
        return new Date(iso).toLocaleTimeString('en-IN', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
            timeZone: appTimezone,
        });
    }

    function parseTs(iso) {
        return iso ? new Date(iso).getTime() : null;
    }

    function itemTimeBounds(item) {
        if (item.type === 'travel') {
            return { start: parseTs(item.start_at), end: parseTs(item.end_at) };
        }
        if (item.action === 'check_out') {
            const t = parseTs(item.occurred_at) ?? parseTs(item.check_out_at);
            return { start: t, end: t };
        }
        if (item.action === 'check_in') {
            const start = parseTs(item.occurred_at) ?? parseTs(item.check_in_at);
            const end = parseTs(item.check_out_at) ?? start;
            return { start, end: end ?? start };
        }
        const start = parseTs(item.check_in_at) ?? parseTs(item.check_out_at);
        const end = parseTs(item.check_out_at) ?? parseTs(item.check_in_at);
        return { start, end: end ?? start };
    }

    function getRouteTimeBounds() {
        if (!routePoints.length) return null;
        const start = parseTs(routePoints[0].recorded_at);
        const end = parseTs(routePoints[routePoints.length - 1].recorded_at);
        if (!start || !end || end <= start) return null;
        return { start, end, span: end - start };
    }

    function sliderValueForTime(ts) {
        const bounds = getRouteTimeBounds();
        if (!bounds || !ts) return 0;
        const ratio = Math.min(1, Math.max(0, (ts - bounds.start) / bounds.span));
        return Math.round(ratio * PLAYBACK_SLIDER_STEPS);
    }

    function timeForSliderValue(val) {
        const bounds = getRouteTimeBounds();
        if (!bounds) return null;
        const ratio = Math.min(1, Math.max(0, val / PLAYBACK_SLIDER_STEPS));
        return bounds.start + ratio * bounds.span;
    }

    function nearestRouteIndexForTime(targetTs) {
        let closest = 0;
        let minDiff = Infinity;
        routePoints.forEach((point, idx) => {
            const ts = parseTs(point.recorded_at);
            if (!ts) return;
            const diff = Math.abs(ts - targetTs);
            if (diff < minDiff) {
                minDiff = diff;
                closest = idx;
            }
        });
        return closest;
    }

    function updateSliderProgress() {
        const val = parseInt(playbackSlider.value, 10) || 0;
        const pct = (val / PLAYBACK_SLIDER_STEPS) * 100;
        playbackSlider.style.setProperty('--progress', pct + '%');
        if (playbackRailFill) playbackRailFill.style.width = pct + '%';
        document.getElementById('playback-progress-label').textContent = Math.round(pct) + '%';

        const currentTs = timeForSliderValue(val);
        if (currentTs) highlightPlaybackEventAtTime(currentTs);
    }

    function playbackEventIcon(variant) {
        const icons = {
            'office-in': 'briefcase',
            'office-out': 'sign-out-alt',
            'visit-in': 'map-marker-alt',
            'visit-out': 'flag-checkered',
            travel: 'truck',
        };
        return icons[variant] || 'circle';
    }

    function playbackEventShortLabel(ev) {
        if (ev.variant === 'visit-in' || ev.variant === 'visit-out') {
            const parts = (ev.label || '').split('—');
            if (parts.length > 1) return parts[0].trim();
        }
        const map = {
            'office-in': 'Office in',
            'office-out': 'Office out',
            'visit-in': 'Visit in',
            'visit-out': 'Visit out',
            travel: 'Travel',
        };
        return map[ev.variant] || ev.label || 'Event';
    }

    function highlightPlaybackEventAtTime(currentTs) {
        if (!playbackEvents.length || !currentTs) return;

        let activeIdx = -1;
        playbackEvents.forEach((ev, idx) => {
            const t = parseTs(ev.time);
            if (!t || t > currentTs) return;
            activeIdx = idx;
        });

        document.querySelectorAll('.playback-event-card').forEach((el, idx) => {
            el.classList.toggle('is-active', idx === activeIdx);
        });

        if (activeIdx >= 0) {
            document.querySelectorAll('.playback-event-card')[activeIdx]
                ?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    }

    function sessionVariant(item) {
        if (item.type === 'office' && item.action === 'check_in') return 'office-in';
        if (item.type === 'office' && item.action === 'check_out') return 'office-out';
        if (item.type === 'visit' && item.action === 'check_in') return 'visit-in';
        if (item.type === 'visit' && item.action === 'check_out') return 'visit-out';
        return item.type === 'office' ? 'office-in' : 'visit-in';
    }

    const sessionThemes = {
        'office-in': {
            card: 'card-office-in',
            pill: 'status-office-in',
            tag: 'tag-office-in',
            icon: 'briefcase',
            iconBg: 'linear-gradient(135deg,#3b82f6,#2563eb)',
        },
        'office-out': {
            card: 'card-office-out',
            pill: 'status-office-out',
            tag: 'tag-office-out',
            icon: 'sign-out-alt',
            iconBg: 'linear-gradient(135deg,#818cf8,#6366f1)',
        },
        'visit-in': {
            card: 'card-visit-in',
            pill: 'status-visit-in',
            tag: 'tag-visit-in',
            icon: 'map-marker-alt',
            iconBg: 'linear-gradient(135deg,#10b981,#059669)',
        },
        'visit-out': {
            card: 'card-visit-out',
            pill: 'status-visit-out',
            tag: 'tag-visit-out',
            icon: 'flag-checkered',
            iconBg: 'linear-gradient(135deg,#fb923c,#f97316)',
        },
    };

    function renderTimeline(items) {
        const list = document.getElementById('timeline-list');
        const empty = document.getElementById('timeline-empty');
        list.innerHTML = '';
        timelineMeta = items;

        if (!items.length) {
            list.classList.add('hidden');
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');
        list.classList.remove('hidden');

        items.forEach((item, idx) => {
            const bounds = itemTimeBounds(item);
            const wrap = document.createElement('div');
            const variant = sessionVariant(item);
            const actionClass = item.action ? ' timeline-action-' + item.action.replace('_', '-') : '';
            wrap.className = 'timeline-item timeline-type-' + item.type + ' timeline-variant-' + variant + actionClass;
            wrap.dataset.index = idx;
            if (bounds.start) wrap.dataset.start = bounds.start;
            if (bounds.end) wrap.dataset.end = bounds.end ?? bounds.start;

            let cardInner = '';
            const segment = '<div class="timeline-segment" aria-hidden="true"><span class="timeline-segment-fill"></span></div>';
            if (item.type === 'travel') {
                cardInner = `
                    <div class="timeline-rail">
                        <div class="timeline-icon" style="background:linear-gradient(135deg,#fbbf24,#f59e0b)"><i class="fas fa-truck"></i></div>
                        ${segment}
                    </div>
                    <div class="timeline-card travel-card">
                        <div class="font-semibold text-slate-800 text-sm">Travel</div>
                        <div class="text-xs text-slate-500 mt-0.5">${item.detail || ''}</div>
                        ${item.time_range ? `<div class="mt-1.5"><span class="status-pill status-amber">${item.time_range}</span></div>` : ''}
                        <div class="active-now-tag hidden"><i class="fas fa-location-arrow text-[9px]"></i> Moving now</div>
                    </div>`;
            } else {
                const theme = sessionThemes[variant] || sessionThemes['visit-in'];
                const isCheckOut = item.action === 'check_out';
                const atLocationTag = (!isCheckOut && item.is_open)
                    ? `<div class="active-now-tag ${theme.tag}"><i class="fas fa-circle text-[6px]"></i> At location</div>`
                    : '<div class="active-now-tag hidden"><i class="fas fa-circle text-[6px]"></i> At location</div>';
                cardInner = `
                    <div class="timeline-rail">
                        <div class="timeline-icon" style="background:${theme.iconBg}"><i class="fas fa-${theme.icon}"></i></div>
                        ${segment}
                    </div>
                    <div class="timeline-card ${theme.card}">
                        <div class="font-semibold text-slate-800 text-sm">${item.title || ''}</div>
                        ${item.address ? `<div class="text-xs text-slate-500 mt-0.5 leading-relaxed">${item.address}</div>` : ''}
                        ${item.status ? `<div class="mt-1.5"><span class="status-pill ${theme.pill}">${item.status}</span></div>` : ''}
                        ${atLocationTag}
                    </div>`;
            }
            wrap.innerHTML = cardInner;
            list.appendChild(wrap);
        });
    }

    function scrollActiveTimelineItem(el) {
        const container = timelineScroll;
        if (!container || !el) return;

        const containerRect = container.getBoundingClientRect();
        const elRect = el.getBoundingClientRect();
        const relativeTop = elRect.top - containerRect.top + container.scrollTop;
        const target = relativeTop - (container.clientHeight - el.offsetHeight) / 2;
        container.scrollTo({ top: Math.max(0, target), behavior: 'smooth' });
    }

    function resolveActiveTimelineIndex(ts) {
        if (!timelineMeta.length || !ts) return -1;

        for (let idx = 0; idx < timelineMeta.length; idx++) {
            const item = timelineMeta[idx];
            if (item.type !== 'travel') continue;
            const start = parseTs(item.start_at);
            const end = parseTs(item.end_at);
            if (start && end && ts >= start && ts <= end) return idx;
        }

        let activeIdx = -1;
        let activeTime = -Infinity;
        timelineMeta.forEach((item, idx) => {
            if (item.type === 'travel') return;
            const eventTime = parseTs(item.occurred_at)
                ?? (item.action === 'check_in' ? parseTs(item.check_in_at) : null)
                ?? (item.action === 'check_out' ? parseTs(item.check_out_at) : null)
                ?? parseTs(item.start_at);
            if (!eventTime || eventTime > ts) return;
            if (eventTime >= activeTime) {
                activeTime = eventTime;
                activeIdx = idx;
            }
        });
        return activeIdx;
    }

    function syncTimelineToTime(recordedAt) {
        if (!recordedAt || !timelineMeta.length) return;
        const ts = parseTs(recordedAt);
        if (!ts) return;

        const items = document.querySelectorAll('#timeline-list .timeline-item');
        const activeIdx = resolveActiveTimelineIndex(ts);

        items.forEach((el, idx) => {
            el.classList.remove('timeline-active', 'timeline-past', 'timeline-done');
            el.querySelector('.active-now-tag')?.classList.add('hidden');

            if (idx === activeIdx) {
                el.classList.add('timeline-active');
                const item = timelineMeta[idx];
                if (item?.action === 'check_in' && item.is_open) {
                    el.querySelector('.active-now-tag')?.classList.remove('hidden');
                }
            } else if (activeIdx >= 0 && idx < activeIdx) {
                el.classList.add('timeline-done', 'timeline-past');
            }
        });

        if (activeIdx >= 0 && items[activeIdx]) {
            scrollActiveTimelineItem(items[activeIdx]);
        }
    }

    function clearTimelineHighlight() {
        document.querySelectorAll('#timeline-list .timeline-item').forEach(el => {
            el.classList.remove('timeline-active', 'timeline-past', 'timeline-done');
            el.querySelector('.active-now-tag')?.classList.add('hidden');
        });
    }

    function buildPlaybackEvents(timeline) {
        const events = [];

        timeline.forEach(item => {
            if (item.type === 'travel' && item.start_at) {
                events.push({
                    variant: 'travel',
                    label: 'Travel',
                    time: item.start_at,
                });
                return;
            }

            if (item.action === 'check_in' || item.action === 'check_out') {
                events.push({
                    variant: sessionVariant(item),
                    label: item.title || item.status || 'Event',
                    time: item.occurred_at,
                });
            }
        });

        return events
            .filter(ev => ev.time)
            .sort((a, b) => (parseTs(a.time) ?? 0) - (parseTs(b.time) ?? 0));
    }

    function playbackMarkerColor(variant) {
        return {
            'office-in': '#3b82f6',
            'office-out': '#6366f1',
            'visit-in': '#10b981',
            'visit-out': '#f97316',
            travel: '#f59e0b',
        }[variant] || '#64748b';
    }

    function renderPlaybackTrack(events) {
        const markersEl = document.getElementById('playback-markers');
        const chipsEl = document.getElementById('playback-event-chips');
        markersEl.innerHTML = '';
        chipsEl.innerHTML = '';
        playbackEvents = events;

        if (!routePoints.length) return;

        const start = parseTs(routePoints[0].recorded_at);
        const end = parseTs(routePoints[routePoints.length - 1].recorded_at);
        const span = (end && start && end > start) ? (end - start) : null;

        events.forEach((ev, idx) => {
            const card = document.createElement('button');
            card.type = 'button';
            card.className = 'playback-event-card playback-card-' + ev.variant;
            card.dataset.eventIndex = String(idx);
            card.innerHTML = `
                <div class="playback-event-icon"><i class="fas fa-${playbackEventIcon(ev.variant)}"></i></div>
                <div class="playback-event-body">
                    <div class="playback-event-time">${formatTime(ev.time)}</div>
                    <div class="playback-event-label">${playbackEventShortLabel(ev)}</div>
                </div>`;
            card.title = ev.label;
            card.addEventListener('click', () => seekPlaybackToTime(ev.time));
            chipsEl.appendChild(card);

            if (!span) return;

            const t = parseTs(ev.time);
            if (!t) return;

            const pct = Math.min(100, Math.max(0, ((t - start) / span) * 100));
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'playback-event-marker';
            dot.style.left = pct + '%';
            dot.style.background = playbackMarkerColor(ev.variant);
            dot.title = `${playbackEventShortLabel(ev)} — ${formatTime(ev.time)}`;
            dot.addEventListener('click', () => seekPlaybackToTime(ev.time));
            markersEl.appendChild(dot);
        });

        updateSliderProgress();
    }

    function seekPlaybackToTime(iso) {
        if (!routePoints.length) return;
        stopPlayback();

        const target = parseTs(iso);
        if (!target) return;

        updatePlayback(nearestRouteIndexForTime(target));
        playbackSlider.value = sliderValueForTime(target);
        updateSliderProgress();
    }

    function playbackIntervalMs() {
        return Math.max(80, Math.round(PLAYBACK_BASE_MS / PLAYBACK_SPEEDS[playbackSpeedIdx]));
    }

    function setPlaybackSpeed(index) {
        playbackSpeedIdx = index;
        playbackSpeedGroup.querySelectorAll('.playback-speed-opt').forEach(btn => {
            btn.classList.toggle('is-active', parseInt(btn.dataset.speed, 10) === index);
        });
        if (playing) {
            stopPlayback();
            startPlayback();
        }
    }

    function renderMap(data) {
        stopPlayback();
        if (routeLine) map.removeLayer(routeLine);
        markerLayer.clearLayers();
        routePoints = data.route || [];
        const displayRoute = (data.route_matched && data.route_display?.length >= 2)
            ? data.route_display
            : routePoints;

        if (displayRoute.length) {
            const latlngs = displayRoute.map(p => [p.lat, p.lng]);
            routeLine = L.polyline(latlngs, { color: '#2563eb', weight: 5, opacity: 0.9, lineCap: 'round', lineJoin: 'round' }).addTo(map);
            map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });

            const matchedBadge = document.getElementById('route-matched-badge');
            if (matchedBadge) {
                matchedBadge.classList.toggle('hidden', !data.route_matched);
            }

            playbackSlider.max = PLAYBACK_SLIDER_STEPS;
            playbackSlider.value = 0;
            updateSliderProgress();
            document.getElementById('playback-bar').classList.toggle('hidden', routePoints.length < 2);
            document.getElementById('playback-start').textContent = formatTime(routePoints[0].recorded_at);
            document.getElementById('playback-end').textContent = formatTime(routePoints[routePoints.length - 1].recorded_at);

            if (playbackMarker) map.removeLayer(playbackMarker);
            playbackMarker = L.marker(latlngs[0], { icon: playbackIconHtml(), zIndexOffset: 1000 }).addTo(map);
            document.getElementById('playback-time').textContent = formatTime(routePoints[0].recorded_at);
            syncTimelineToTime(routePoints[0].recorded_at);
            renderPlaybackTrack(buildPlaybackEvents(data.timeline || []));
        } else {
            document.getElementById('playback-bar').classList.add('hidden');
            document.getElementById('route-matched-badge')?.classList.add('hidden');
            document.getElementById('playback-markers').innerHTML = '';
            document.getElementById('playback-event-chips').innerHTML = '';
            clearTimelineHighlight();
        }

        (data.markers || []).forEach(m => {
            L.marker([m.lat, m.lng], { icon: iconHtml(m.type) })
                .bindPopup(`<strong>${m.label || 'Stop'}</strong>`).addTo(markerLayer);
        });
    }

    async function loadTracking() {
        const employeeId = employeeSelect.value;
        const date = trackDate.value;
        if (!employeeId) return;

        document.getElementById('display-date').textContent = formatDisplayDate(date);

        const response = await fetch(`${trackingUrl}?employee_id=${employeeId}&date=${date}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const payload = await response.json();
        if (!payload.success) return;

        const data = payload.data;
        document.getElementById('employee-header').classList.remove('hidden');
        document.getElementById('employee-name').textContent = data.employee.name;
        document.getElementById('employee-code').textContent = data.employee.employee_id + (data.employee.designation ? ' · ' + data.employee.designation : '');
        document.getElementById('employee-avatar').textContent = (data.employee.name || '?').charAt(0).toUpperCase();

        document.getElementById('summary-card').classList.remove('hidden');
        document.getElementById('summary-travel').textContent = data.summary.travel_time_label;
        document.getElementById('summary-distance').textContent = data.summary.distance_label;
        document.getElementById('summary-visits').textContent = data.summary.visits;

        renderTimeline(data.timeline || []);
        renderMap(data);
        setTimeout(() => map.invalidateSize(), 200);
    }

    function shiftDate(days) {
        const d = parseCalendarDate(trackDate.value);
        d.setDate(d.getDate() + days);
        trackDate.value = formatDateInput(d);
        loadTracking();
    }

    function updatePlayback(index) {
        if (!routePoints.length || !playbackMarker) return;
        const point = routePoints[index];
        playbackMarker.setLatLng([point.lat, point.lng]);
        document.getElementById('playback-time').textContent = formatTime(point.recorded_at);
        const ts = parseTs(point.recorded_at);
        if (ts) playbackSlider.value = sliderValueForTime(ts);
        updateSliderProgress();
        syncTimelineToTime(point.recorded_at);
    }

    function updatePlaybackFromSlider(val) {
        if (!routePoints.length || !playbackMarker) return;
        const targetTs = timeForSliderValue(val);
        if (!targetTs) return;
        const index = nearestRouteIndexForTime(targetTs);
        const point = routePoints[index];
        playbackMarker.setLatLng([point.lat, point.lng]);
        document.getElementById('playback-time').textContent = formatTime(point.recorded_at);
        playbackSlider.value = val;
        updateSliderProgress();
        syncTimelineToTime(point.recorded_at);
    }

    function startPlayback() {
        if (routePoints.length < 2) return;
        playing = true;
        playbackToggle.classList.add('is-playing');
        playbackIcon.className = 'fas fa-pause text-sm';
        document.getElementById('playback-live-badge').classList.remove('hidden');
        const sliderTs = timeForSliderValue(parseInt(playbackSlider.value, 10));
        let index = sliderTs ? nearestRouteIndexForTime(sliderTs) : 0;
        playbackTimer = setInterval(() => {
            index++;
            if (index >= routePoints.length) { stopPlayback(); return; }
            updatePlayback(index);
        }, playbackIntervalMs());
    }

    function stopPlayback() {
        playing = false;
        playbackToggle.classList.remove('is-playing');
        playbackIcon.className = 'fas fa-play text-sm ml-0.5';
        document.getElementById('playback-live-badge').classList.add('hidden');
        if (playbackTimer) clearInterval(playbackTimer);
        playbackTimer = null;
    }

    loadBtn.addEventListener('click', loadTracking);
    employeeSelect.addEventListener('change', loadTracking);
    trackDate.addEventListener('change', loadTracking);
    document.getElementById('date-prev').addEventListener('click', () => shiftDate(-1));
    document.getElementById('date-next').addEventListener('click', () => shiftDate(1));
    document.getElementById('map-zoom-in').addEventListener('click', () => map.zoomIn());
    document.getElementById('map-zoom-out').addEventListener('click', () => map.zoomOut());
    document.getElementById('map-recenter').addEventListener('click', () => {
        if (routeLine) map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });
    });
    playbackSlider.addEventListener('input', e => {
        stopPlayback();
        updatePlaybackFromSlider(parseInt(e.target.value, 10));
    });
    playbackToggle.addEventListener('click', () => playing ? stopPlayback() : startPlayback());
    playbackSpeedGroup.querySelectorAll('.playback-speed-opt').forEach(btn => {
        btn.addEventListener('click', () => setPlaybackSpeed(parseInt(btn.dataset.speed, 10)));
    });
    document.getElementById('playback-reset').addEventListener('click', () => { stopPlayback(); updatePlayback(0); });

    if (app.dataset.initialEmployee) loadTracking();
});
</script>
@endsection
