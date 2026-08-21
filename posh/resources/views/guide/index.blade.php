@extends('layouts.guide-standalone')

@section('title', 'User Guide')

@php
    $guideImg = function (string $file) {
        $path = public_path('images/guide/' . $file);
        return asset('images/guide/' . $file) . (is_file($path) ? '?v=' . filemtime($path) : '');
    };
    $guideBannerMain = $guideImg('posh-guide-hero.png');
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    .font-display { font-family: 'Outfit', 'Inter', system-ui, sans-serif; }

    /* Isarva-inspired light premium theme */
    .docs-page-bg {
        background-color: #fafafa;
        background-image:
            radial-gradient(ellipse 70% 50% at 10% -10%, rgba(16, 185, 129, 0.08), transparent),
            radial-gradient(ellipse 50% 40% at 90% 20%, rgba(13, 148, 136, 0.06), transparent),
            radial-gradient(ellipse 40% 30% at 50% 100%, rgba(5, 150, 105, 0.04), transparent);
    }

    .docs-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 30%, #f0fdfa 60%, #ffffff 100%);
        padding-top: 2rem;
        padding-bottom: 3rem;
    }
    @media (min-width: 1024px) {
        .docs-hero { padding-top: 3rem; padding-bottom: 4rem; }
    }
    .docs-hero-orb-1 {
        position: absolute;
        top: -8rem; left: -8rem;
        width: 28rem; height: 28rem;
        border-radius: 9999px;
        background: rgba(167, 243, 208, 0.55);
        filter: blur(80px);
        pointer-events: none;
    }
    .docs-hero-orb-2 {
        position: absolute;
        top: 40%; right: -5rem;
        width: 24rem; height: 24rem;
        border-radius: 9999px;
        background: rgba(153, 246, 228, 0.5);
        filter: blur(70px);
        pointer-events: none;
    }
    .docs-hero-grid {
        position: absolute;
        inset: 0;
        opacity: 0.35;
        background-image: linear-gradient(rgba(5,150,105,.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(5,150,105,.04) 1px, transparent 1px);
        background-size: 40px 40px;
        mask-image: linear-gradient(180deg, black 0%, transparent 90%);
        pointer-events: none;
    }
    .docs-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.5rem 1.125rem;
        border-radius: 9999px;
        background: #d1fae5;
        color: #065f46;
        font-size: 0.8125rem;
        font-weight: 600;
        border: 1px solid #a7f3d0;
    }
    .docs-eyebrow-dot {
        position: relative;
        display: flex;
        height: 0.625rem;
        width: 0.625rem;
    }
    .docs-eyebrow-dot::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        background: #10b981;
        animation: docs-ping 1.8s cubic-bezier(0,0,.2,1) infinite;
        opacity: 0.6;
    }
    .docs-eyebrow-dot::after {
        content: '';
        position: relative;
        display: block;
        height: 0.625rem;
        width: 0.625rem;
        border-radius: 9999px;
        background: #059669;
    }
    @keyframes docs-ping {
        0% { transform: scale(1); opacity: 0.6; }
        75%, 100% { transform: scale(2.2); opacity: 0; }
    }
    .docs-title-accent {
        background: linear-gradient(90deg, #059669, #0d9488, #059669);
        background-size: 200% auto;
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        animation: docs-shimmer 4s linear infinite;
    }
    @keyframes docs-shimmer {
        0% { background-position: 0% center; }
        100% { background-position: 200% center; }
    }
    .docs-feature-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.875rem;
        border-radius: 9999px;
        background: #fff;
        border: 1px solid #e5e7eb;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        box-shadow: 0 1px 2px rgba(0,0,0,.04);
    }
    .docs-search-wrap:focus-within {
        border-color: #6ee7b7;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    }
    .docs-btn-primary {
        background: linear-gradient(135deg, #10b981, #0d9488);
        color: #fff;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .docs-btn-primary:hover {
        background: linear-gradient(135deg, #059669, #0f766e);
        box-shadow: 0 8px 20px -6px rgba(5, 150, 105, 0.45);
    }
    .docs-hero-visual {
        position: relative;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(6, 78, 59, 0.18);
        border: 1px solid rgba(255,255,255,0.8);
    }
    .docs-float-badge {
        position: absolute;
        z-index: 10;
        background: #fff;
        border-radius: 1rem;
        padding: 0.875rem 1.125rem;
        box-shadow: 0 10px 30px -8px rgba(0,0,0,.12), 0 0 0 1px rgba(0,0,0,.04);
        animation: docs-float 5s ease-in-out infinite;
    }
    .docs-float-badge--1 { top: 1rem; right: -0.5rem; animation-delay: 0s; }
    .docs-float-badge--2 { bottom: 1.5rem; left: -0.75rem; animation-delay: 1.2s; }
    @keyframes docs-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    .docs-stat-box {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 1.25rem;
        padding: 1.5rem;
        text-align: center;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    }
    .docs-stat-box:hover {
        transform: translateY(-2px);
        border-color: #a7f3d0;
        box-shadow: 0 12px 28px -10px rgba(5, 150, 105, 0.2);
    }
    .docs-stat-num {
        font-family: 'Outfit', 'Inter', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        color: #059669;
    }
    .docs-stat-label {
        margin-top: 0.375rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #6b7280;
    }

    .docs-toc { position: sticky; top: 5.5rem; max-height: calc(100vh - 6rem); overflow-y: auto; scrollbar-width: thin; }
    .docs-toc::-webkit-scrollbar { width: 4px; }
    .docs-toc::-webkit-scrollbar-thumb { background: #a7f3d0; border-radius: 999px; }

    /* Docs shell layout */
    .docs-shell {
        display: grid;
        grid-template-columns: 1fr;
        align-items: start;
        background: #fff;
        border-radius: 1.5rem;
        box-shadow: 0 4px 24px -8px rgba(0,0,0,.08);
        border: 1px solid #e5e7eb;
    }
    @media (min-width: 1024px) {
        .docs-shell {
            grid-template-columns: 320px minmax(0, 1fr);
        }
    }
    .docs-sidebar {
        display: none;
        flex-direction: column;
        background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
        border-right: 1px solid #e5e7eb;
        min-height: 0;
        border-radius: 1.5rem 0 0 1.5rem;
        overflow: visible;
    }
    @media (min-width: 1024px) {
        .docs-sidebar {
            display: flex;
            position: sticky;
            top: 3.5rem;
            height: calc(100vh - 3.5rem);
        }
    }
    .docs-sidebar-head {
        flex-shrink: 0;
        padding: 1.25rem 1.25rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
    }
    .docs-sidebar-progress {
        height: 4px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
        margin-top: 0.875rem;
    }
    .docs-sidebar-progress-fill {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #10b981, #0d9488);
        border-radius: 999px;
        transition: width 0.35s ease;
    }
    /* Consistent section spacing */
    .docs-section-block {
        padding-top: 2.5rem;
        padding-bottom: 2.5rem;
    }
    .docs-section-block--tight {
        padding-top: 1.5rem;
        padding-bottom: 2.5rem;
    }

    .docs-sidebar-body {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .docs-sidebar-scroll {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 1.25rem 1rem 0.75rem 1.625rem;
        scrollbar-width: thin;
        scrollbar-color: #a7f3d0 transparent;
    }
    .docs-sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .docs-sidebar-scroll::-webkit-scrollbar-thumb { background: #a7f3d0; border-radius: 999px; }
    .docs-sidebar-foot {
        flex-shrink: 0;
        padding: 0.875rem 1.25rem 1.25rem;
        border-top: 1px solid #f3f4f6;
        background: #fafafa;
    }
    .docs-sidebar-group-title {
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #9ca3af;
        margin: 0 0 0.75rem 0;
        padding-left: 2.75rem;
    }
    .docs-sidebar-group-title + .docs-toc-list {
        margin-bottom: 0;
    }
    .docs-toc-list + .docs-sidebar-group-title {
        margin-top: 1.5rem;
    }
    .docs-toc-list {
        position: relative;
        list-style: none;
        margin: 0;
        padding: 0 0 0 0.25rem;
    }
    .docs-toc-item {
        position: relative;
        margin: 0;
    }
    .docs-toc-link {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 0.625rem;
        min-height: 2.75rem;
        padding: 0.5rem 0.375rem 0.5rem 0.125rem;
        border-radius: 0.75rem;
        text-decoration: none;
        transition: background 0.15s, color 0.15s;
    }
    .docs-toc-link:hover { background: rgba(236, 253, 245, 0.7); }
    .docs-toc-rail {
        position: relative;
        width: 2rem;
        height: 2.75rem;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .docs-toc-item:not(:last-child) .docs-toc-rail::after {
        content: '';
        position: absolute;
        left: 50%;
        top: calc(50% + 0.875rem);
        width: 2px;
        height: 1rem;
        background: #a7f3d0;
        transform: translateX(-50%);
        z-index: 0;
    }
    .docs-toc-num {
        position: relative;
        z-index: 2;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 700;
        line-height: 1;
        color: #6b7280;
        background: #fff;
        border: 2px solid #a7f3d0;
        transition: background 0.2s, border-color 0.2s, color 0.2s;
    }
    .docs-toc-text {
        flex: 1 1 0;
        min-width: 0;
        font-size: 0.8125rem;
        line-height: 1.35;
        font-weight: 500;
        color: #4b5563;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .docs-toc-link.is-active {
        background: linear-gradient(90deg, #ecfdf5 0%, rgba(236,253,245,0.4) 100%);
    }
    .docs-toc-link.is-active .docs-toc-num {
        background: linear-gradient(135deg, #10b981, #0d9488);
        border-color: #059669;
        color: #fff;
    }
    .docs-toc-link.is-active .docs-toc-text {
        color: #047857;
        font-weight: 600;
    }
    .docs-sidebar-ref-link {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        min-height: 2.5rem;
        padding: 0.5rem 0.625rem 0.5rem 2.875rem;
        border-radius: 0.625rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #4b5563;
        text-decoration: none;
        transition: background 0.15s, color 0.15s;
    }
    .docs-sidebar-ref-link:hover {
        background: #ecfdf5;
        color: #047857;
    }
    .docs-sidebar-ref-link i {
        width: 1.5rem;
        text-align: center;
        color: #10b981;
        font-size: 0.75rem;
    }
    .docs-main {
        min-width: 0;
        background: #fafafa;
        border-radius: 0 1.5rem 1.5rem 0;
        overflow: hidden;
    }
    .docs-main-inner {
        padding: 2rem 1.5rem;
    }
    @media (min-width: 1024px) {
        .docs-main-inner { padding: 2.5rem; }
    }
    .docs-main-header {
        display: none;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    @media (min-width: 1024px) {
        .docs-main-header { display: block; }
    }
    .docs-back-top {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        justify-content: center;
        padding: 0.625rem;
        border-radius: 0.75rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #6b7280;
        background: #fff;
        border: 1px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.15s;
    }
    .docs-back-top:hover {
        color: #047857;
        border-color: #a7f3d0;
        background: #ecfdf5;
    }

    /* Legacy toc nav inside mobile drawer */
    .docs-toc-nav { border-radius: 0; border: 0; background: transparent; box-shadow: none; }
    .docs-toc a.is-active:not(.docs-toc-link) {
        color: #047857;
        font-weight: 600;
        background: #ecfdf5;
        border-radius: 0.625rem;
    }
    .docs-article { scroll-margin-top: 5.5rem; }
    @keyframes docs-search-flash {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
        40% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.2); }
        100% { box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.12); }
    }
    .docs-search-flash { animation: docs-search-flash 2s ease-out; }

    .docs-role-wrap { position: relative; z-index: 20; margin-top: 0; padding-top: 0.25rem; }
    .docs-role-bar {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 1.25rem;
        box-shadow: 0 4px 24px -6px rgba(0,0,0,.08);
    }
    .docs-role-bar .docs-role-pill.is-active {
        background: linear-gradient(135deg, #10b981, #0d9488);
        color: #fff;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
        border-color: transparent;
    }
    .docs-role-bar .docs-role-pill:not(.is-active) {
        background: #f9fafb;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }
    .docs-role-bar .docs-role-pill:not(.is-active):hover {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }

    .docs-section-label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #059669;
        margin: 0 0 1.25rem 0;
    }
    .docs-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, #a7f3d0, transparent);
        min-width: 2rem;
    }

    .docs-topic-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.25rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        padding: 1.375rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .docs-topic-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: var(--topic-accent, #10b981);
        opacity: 0;
        transition: opacity 0.2s;
    }
    .docs-topic-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 32px -12px rgba(5, 150, 105, 0.15);
        border-color: #a7f3d0;
    }
    .docs-topic-card:hover::before { opacity: 1; }
    .docs-topic-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.875rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .docs-jump-strip {
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid #e5e7eb;
    }
    .docs-jump-scroll {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #34d399 #d1fae5;
        padding-bottom: 0.5rem;
    }
    .docs-jump-scroll::-webkit-scrollbar { height: 8px; }
    .docs-jump-scroll::-webkit-scrollbar-track {
        margin: 0 0.5rem;
        background: #ecfdf5;
        border-radius: 999px;
        border: 1px solid #a7f3d0;
    }
    .docs-jump-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #10b981, #0d9488);
        border-radius: 999px;
        border: 2px solid #ecfdf5;
    }
    .docs-jump-fade-l, .docs-jump-fade-r {
        position: absolute;
        top: 0; bottom: 0.5rem;
        width: 2.5rem;
        pointer-events: none;
        z-index: 1;
    }
    .docs-jump-fade-l { left: 0; background: linear-gradient(90deg, rgba(255,255,255,.95), transparent); }
    .docs-jump-fade-r { right: 0; background: linear-gradient(270deg, rgba(255,255,255,.95), transparent); }
    .docs-jump-link.is-active {
        background: #ecfdf5;
        color: #047857;
        font-weight: 600;
        border-color: #a7f3d0;
    }

    .docs-ref-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow: hidden;
        border-radius: 1.25rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .docs-ref-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 40px -12px rgba(5, 150, 105, 0.12);
        border-color: #a7f3d0;
    }
    .docs-timeline-row {
        display: flex;
        gap: 0.75rem;
        padding-bottom: 0.875rem;
        margin-bottom: 0.875rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .docs-timeline-row:last-child { border-bottom: 0; margin-bottom: 0; padding-bottom: 0; }
    .docs-timeline-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 9999px;
        background: #10b981;
        margin-top: 0.45rem;
        margin-left: 0.25rem;
        flex-shrink: 0;
        outline: 3px solid rgba(16, 185, 129, 0.18);
        outline-offset: 0;
    }

    .docs-article {
        border-radius: 1.25rem;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e5e7eb;
        transition: box-shadow 0.25s ease;
    }
    .docs-article:hover {
        box-shadow: 0 20px 50px -15px rgba(5, 150, 105, 0.1);
        border-color: #d1fae5;
    }
    .docs-article-header {
        background: linear-gradient(135deg, #fafafa 0%, #ecfdf5 50%, #f0fdfa 100%);
        border-bottom: 1px solid #e5e7eb;
    }
    .docs-article-num {
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #059669;
        margin-bottom: 0.25rem;
    }
    .docs-toc-nav {
        border-radius: 1.25rem;
        border: 1px solid #e5e7eb;
        background: #fff;
        box-shadow: 0 4px 20px -6px rgba(0,0,0,.06);
    }

    .prose-guide p { color: #374151; }
    .prose-guide a:not(.docs-cta-link) { color: #059669; font-weight: 500; }
    .prose-guide a:not(.docs-cta-link):hover { color: #047857; text-decoration: underline; }
    .docs-cta-link { color: #fff !important; text-decoration: none !important; }
    .docs-cta-link:hover { color: #fff !important; text-decoration: none !important; }

    @media (min-width: 1024px) {
        .docs-ref-card { height: 23rem; }
    }
    .docs-ref-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        margin-right: -0.25rem;
        padding: 0.125rem 0.5rem 0 0.375rem;
        scrollbar-width: thin;
        scrollbar-color: #6ee7b7 transparent;
    }
    .docs-ref-scroll::-webkit-scrollbar { width: 6px; height: 0; }
    .docs-ref-scroll::-webkit-scrollbar-track { background: transparent; }
    .docs-ref-scroll::-webkit-scrollbar-thumb {
        background: #a7f3d0;
        border-radius: 999px;
        border: 2px solid transparent;
        background-clip: padding-box;
    }
    .docs-ref-scroll::-webkit-scrollbar-thumb:hover { background: #6ee7b7; background-clip: padding-box; }
    .docs-ref-scroll dl, .docs-ref-scroll dd, .docs-ref-scroll li { overflow-wrap: anywhere; word-break: break-word; }
    #docs-mobile-toc.open { transform: translateX(0); }
</style>
@endpush

@section('body')
<main class="pt-14">
    {{-- Hero — Isarva-style light premium --}}
    <section class="docs-hero">
        <div class="docs-hero-orb-1" aria-hidden="true"></div>
        <div class="docs-hero-orb-2" aria-hidden="true"></div>
        <div class="docs-hero-grid" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div class="text-center lg:text-left">
                    <p class="docs-eyebrow mb-6">
                        <span class="docs-eyebrow-dot" aria-hidden="true"></span>
                        Premium POSH compliance guide
                    </p>
                    <h1 class="font-display text-[clamp(2rem,5vw,3.25rem)] font-extrabold text-gray-900 leading-[1.08] tracking-tight mb-5">
                        Your complete guide to
                        <span class="docs-title-accent"> safe workplaces</span>
                    </h1>
                    <p class="text-base sm:text-lg text-gray-600 leading-relaxed max-w-xl mx-auto lg:mx-0 mb-8">
                        Step-by-step help for {{ config('posh.product_name') }} — from filing complaints to IC inquiry, compliance, and annual reporting.
                    </p>

                    <div class="flex flex-wrap justify-center lg:justify-start gap-2 mb-8">
                        <span class="docs-feature-pill"><i class="fas fa-user-shield text-emerald-600"></i> Confidential</span>
                        <span class="docs-feature-pill"><i class="fas fa-scale-balanced text-emerald-600"></i> Law aligned</span>
                        <span class="docs-feature-pill"><i class="fas fa-list-check text-emerald-600"></i> Step-by-step</span>
                        <span class="docs-feature-pill"><i class="fas fa-users text-emerald-600"></i> Role-based</span>
                    </div>

                    <div class="max-w-xl mx-auto lg:mx-0">
                        <form id="guide-search-form" class="flex flex-col sm:flex-row gap-2" role="search" autocomplete="off">
                            <div class="docs-search-wrap flex flex-1 items-center gap-3 rounded-xl bg-white px-4 py-3 border border-gray-200 min-w-0 transition">
                                <i class="fas fa-magnifying-glass text-emerald-600 shrink-0"></i>
                                <input type="text" id="guide-search" name="q" autocomplete="off" placeholder="Search articles, topics, glossary…"
                                    class="flex-1 min-w-0 border-0 bg-transparent text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0 outline-none">
                                <button type="button" id="guide-search-clear" class="hidden shrink-0 text-gray-400 hover:text-gray-600 p-1" aria-label="Clear search">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>
                            <button type="submit" id="guide-search-btn"
                                class="docs-btn-primary shrink-0 inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold shadow-md">
                                <i class="fas fa-magnifying-glass text-xs"></i>
                                Search
                            </button>
                        </form>
                        <p id="guide-search-status" class="mt-2 text-xs text-gray-500 min-h-[1.25rem]" aria-live="polite"></p>
                    </div>

                    <div class="mt-6 flex justify-center lg:justify-start">
                        <a href="#guide-articles" class="docs-btn-primary inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold shadow-md">
                            Explore articles <i class="fas fa-arrow-down text-xs"></i>
                        </a>
                    </div>
                </div>

                <div class="relative max-w-lg mx-auto lg:mx-0 lg:ml-auto w-full">
                    <div class="docs-hero-visual aspect-[4/3] relative">
                        <img src="{{ $guideBannerMain }}" alt="Inclusive workplace — POSH compliance"
                            class="absolute inset-0 h-full w-full object-cover object-center" loading="eager">
                    </div>
                    <div class="docs-float-badge docs-float-badge--1 hidden sm:block">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 text-lg"><i class="fas fa-user-shield"></i></span>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Confidential</p>
                                <p class="text-xs font-semibold text-emerald-600">Protected process ✓</p>
                            </div>
                        </div>
                    </div>
                    <div class="docs-float-badge docs-float-badge--2 hidden sm:block">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-100 text-teal-700 text-lg"><i class="fas fa-scale-balanced"></i></span>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Law aligned</p>
                                <p class="text-xs font-semibold text-emerald-600">POSH Act, 2013</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats strip — like Isarva metrics --}}
    <section class="docs-section-block--tight">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="docs-stat-box">
                    <p class="docs-stat-num">{{ count($sections) }}+</p>
                    <p class="docs-stat-label">Guide articles</p>
                </div>
                <div class="docs-stat-box">
                    <p class="docs-stat-num">9</p>
                    <p class="docs-stat-label">IC inquiry steps</p>
                </div>
                <div class="docs-stat-box">
                    <p class="docs-stat-num">100%</p>
                    <p class="docs-stat-label">Law coverage</p>
                </div>
                <div class="docs-stat-box">
                    <p class="docs-stat-num">24/7</p>
                    <p class="docs-stat-label">Self-serve access</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Role switcher — floating card --}}
    @if(count($roleTabs) > 1)
    <div class="docs-role-wrap mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8 pb-2">
        <div class="docs-role-bar px-5 py-4 sm:px-6">
            <p class="docs-section-label"><span>Choose your guide</span></p>
            <div class="flex flex-wrap gap-2">
                @foreach($roleTabs as $key => $label)
                    <a href="{{ route('guide.index', ['role' => $key]) }}"
                        class="docs-role-pill inline-flex items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition {{ $activeTab === $key ? 'is-active' : '' }}">
                        @if($key === 'employee')<i class="fas fa-user text-xs"></i>
                        @elseif($key === 'ic')<i class="fas fa-people-group text-xs"></i>
                        @else<i class="fas fa-gear text-xs"></i>@endif
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Quick topics --}}
    <section class="docs-section-block {{ count($roleTabs) > 1 ? '' : 'docs-section-block--tight' }}">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
            <p class="docs-section-label"><span>What we cover</span></p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="#guide-articles" class="docs-topic-card group block no-underline" style="--topic-accent: #10b981;">
                    <div class="flex items-center gap-4">
                        <span class="docs-topic-icon bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors"><i class="fas fa-file-circle-plus"></i></span>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">File & track</p>
                            <p class="text-xs text-gray-500 mt-0.5">Complaints & case status</p>
                        </div>
                    </div>
                </a>
                <a href="#guide-articles" class="docs-topic-card group block no-underline" style="--topic-accent: #0d9488;">
                    <div class="flex items-center gap-4">
                        <span class="docs-topic-icon bg-teal-100 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-colors"><i class="fas fa-list-check"></i></span>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">IC operate</p>
                            <p class="text-xs text-gray-500 mt-0.5">9-step inquiry wizard</p>
                        </div>
                    </div>
                </a>
                <a href="#guide-articles" class="docs-topic-card group block no-underline" style="--topic-accent: #059669;">
                    <div class="flex items-center gap-4">
                        <span class="docs-topic-icon bg-green-100 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors"><i class="fas fa-clipboard-check"></i></span>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">Stay compliant</p>
                            <p class="text-xs text-gray-500 mt-0.5">Duties & annual reports</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- Quick jump strip — mobile/tablet only (sidebar on desktop) --}}
    <section class="docs-jump-strip lg:hidden sticky top-14 z-40 shadow-sm">
        <div class="relative mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
            <span class="docs-jump-fade-l hidden sm:block" aria-hidden="true"></span>
            <span class="docs-jump-fade-r hidden sm:block" aria-hidden="true"></span>
            <div class="docs-jump-scroll flex items-center gap-3 py-3 pr-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 shrink-0 pl-0.5">Jump to</span>
                <span class="h-4 w-px bg-emerald-200 shrink-0" aria-hidden="true"></span>
                @foreach($sections as $section)
                    <a href="#{{ $section['id'] }}"
                        class="docs-jump-link shrink-0 text-xs font-medium text-gray-600 hover:text-emerald-700 px-3 py-1.5 rounded-full hover:bg-emerald-50 border border-transparent hover:border-emerald-100 transition whitespace-nowrap"
                        data-guide-search="{{ \App\Support\PoshGuide::sectionSearchText($section) }}">
                        {{ $section['title'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8 docs-section-block pt-0">

        {{-- Mobile TOC toggle --}}
        <button type="button" id="docs-toc-open" class="lg:hidden fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-emerald-600 to-teal-600 text-white shadow-xl shadow-emerald-600/35 ring-4 ring-white transition hover:scale-105 active:scale-95">
            <i class="fas fa-list text-lg"></i>
        </button>

        {{-- Mobile TOC drawer --}}
        <div id="docs-mobile-toc" class="lg:hidden fixed inset-y-0 left-0 z-50 w-80 max-w-[88vw] bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 flex flex-col">
            <div class="docs-sidebar-head pt-16 px-5 pb-4">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-1">Guide navigation</p>
                <p class="text-sm font-semibold text-gray-900">{{ count($sections) }} articles · {{ config('posh.user_roles.' . auth()->user()->posh_role, 'User') }}</p>
                <div class="docs-sidebar-progress" aria-hidden="true">
                    <div id="docs-mobile-progress" class="docs-sidebar-progress-fill"></div>
                </div>
            </div>
            <div class="docs-sidebar-body flex-1">
                <div class="docs-sidebar-scroll">
                <p class="docs-sidebar-group-title">Articles</p>
                <ul class="docs-toc-list">
                    @foreach($sections as $index => $section)
                        <li class="docs-toc-item">
                            <a href="#{{ $section['id'] }}"
                                class="docs-toc-link"
                                title="{{ $section['title'] }}"
                                data-guide-search="{{ \App\Support\PoshGuide::sectionSearchText($section) }}">
                                <span class="docs-toc-rail"><span class="docs-toc-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span></span>
                                <span class="docs-toc-text">{{ $section['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="docs-sidebar-group-title">Reference</p>
                <div class="space-y-0.5">
                    <a href="#reference-timelines" class="docs-sidebar-ref-link"><i class="fas fa-clock"></i> Statutory timelines</a>
                    <a href="#reference-glossary" class="docs-sidebar-ref-link"><i class="fas fa-book"></i> Glossary</a>
                </div>
                </div>
            </div>
            <div class="docs-sidebar-foot px-4 pb-6">
                <button type="button" id="docs-back-top-mobile" class="docs-back-top"><i class="fas fa-arrow-up text-xs"></i> Back to top</button>
            </div>
        </div>
        <div id="docs-toc-backdrop" class="lg:hidden fixed inset-0 z-40 bg-gray-900/30 backdrop-blur-sm hidden"></div>

        {{-- Docs shell: sidebar + main --}}
        <div class="docs-shell">
            {{-- Desktop sidebar --}}
            <aside class="docs-sidebar" aria-label="Guide sidebar">
                <div class="docs-sidebar-head">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-1">Guide navigation</p>
                    <p class="text-base font-display font-bold text-gray-900 leading-tight">Browse articles</p>
                    <p class="text-xs text-gray-500 mt-1">{{ count($sections) }} articles · {{ config('posh.user_roles.' . auth()->user()->posh_role, 'User') }}</p>
                    <div class="docs-sidebar-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-label="Reading progress">
                        <div id="docs-sidebar-progress" class="docs-sidebar-progress-fill"></div>
                    </div>
                </div>
                <div class="docs-sidebar-body">
                    <div class="docs-sidebar-scroll">
                    <p class="docs-sidebar-group-title">Articles</p>
                    <ul class="docs-toc-list">
                        @foreach($sections as $index => $section)
                            <li class="docs-toc-item">
                                <a href="#{{ $section['id'] }}"
                                    class="docs-toc-link"
                                    title="{{ $section['title'] }}"
                                    data-guide-search="{{ \App\Support\PoshGuide::sectionSearchText($section) }}">
                                    <span class="docs-toc-rail"><span class="docs-toc-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span></span>
                                    <span class="docs-toc-text">{{ $section['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <p class="docs-sidebar-group-title">Reference</p>
                    <div class="space-y-0.5">
                        <a href="#reference-timelines" class="docs-sidebar-ref-link"><i class="fas fa-clock"></i> Statutory timelines</a>
                        <a href="#reference-glossary" class="docs-sidebar-ref-link"><i class="fas fa-book"></i> Glossary</a>
                    </div>
                    </div>
                </div>
                <div class="docs-sidebar-foot">
                    <button type="button" id="docs-back-top" class="docs-back-top"><i class="fas fa-arrow-up text-xs"></i> Back to top</button>
                </div>
            </aside>

            {{-- Main content --}}
            <div class="docs-main">
                <div class="docs-main-inner">
                    <div class="docs-main-header">
                        <h2 class="text-xl font-display font-bold text-gray-900">Knowledge base</h2>
                        <p class="text-sm text-gray-500 mt-1">Reference materials and step-by-step guides for your role.</p>
                    </div>

                    {{-- Reference cards --}}
                    <div class="mb-10" id="guide-reference">
                        <p class="docs-section-label lg:hidden"><span>Reference</span></p>
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 lg:items-stretch">
                            <div id="reference-timelines" class="docs-ref-card group p-6 lg:p-7 shadow-sm scroll-mt-24">
                                <h2 class="shrink-0 text-lg font-bold text-gray-900 flex items-center gap-3 mb-4">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-500/20"><i class="fas fa-clock text-sm"></i></span>
                                    Statutory timelines
                                </h2>
                                <div class="docs-ref-scroll">
                                    <ul>
                                        @foreach($timelines as $t)
                                            <li class="docs-ref-item docs-timeline-row"
                                                data-guide-search="{{ strtolower($t['label'] . ' ' . $t['days'] . ' ' . ($t['law'] ?? '')) }}">
                                                <span class="docs-timeline-dot" aria-hidden="true"></span>
                                                <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-4 text-sm min-w-0 flex-1">
                                                    <span class="text-gray-700">{{ $t['label'] }}</span>
                                                    <span class="font-bold text-emerald-700 sm:shrink-0 sm:text-right">{{ $t['days'] }}</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div id="reference-glossary" class="docs-ref-card group p-6 lg:p-7 shadow-sm scroll-mt-24">
                                <h2 class="shrink-0 text-lg font-bold text-gray-900 flex items-center gap-3 mb-4">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-600 to-emerald-600 text-white shadow-md shadow-teal-500/20"><i class="fas fa-book text-sm"></i></span>
                                    Glossary
                                </h2>
                                <div class="docs-ref-scroll">
                                    <dl>
                                        @foreach($glossary as $g)
                                            <div class="docs-ref-item docs-timeline-row" data-guide-search="{{ strtolower($g['term'] . ' ' . $g['definition']) }}">
                                                <span class="docs-timeline-dot" aria-hidden="true"></span>
                                                <div class="min-w-0 flex-1">
                                                    <dt class="text-sm font-bold text-emerald-800">{{ $g['term'] }}</dt>
                                                    <dd class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $g['definition'] }}</dd>
                                                </div>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Articles --}}
                    <div class="space-y-6 min-w-0" id="guide-articles">
                        <p class="docs-section-label"><span>Articles</span></p>
                        @forelse($sections as $index => $section)
                            <article id="{{ $section['id'] }}"
                                class="docs-article shadow-sm bg-white"
                                data-guide-search="{{ \App\Support\PoshGuide::sectionSearchText($section) }}"
                                data-article-index="{{ $index }}">
                                <header class="docs-article-header relative px-5 py-6 sm:px-8 sm:py-8 border-l-4 border-l-emerald-500">
                                    <div class="flex items-start gap-4">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-500/20">
                                            <i class="fas {{ $section['icon'] ?? 'fa-book' }}"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="docs-article-num">Article {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</p>
                                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight font-display">{{ $section['title'] }}</h2>
                                            @if(!empty($section['summary']))
                                                <p class="mt-2 text-sm sm:text-base text-gray-600 leading-relaxed">{{ $section['summary'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </header>
                                <div class="px-5 py-6 sm:px-8 sm:py-8 prose-guide max-w-none bg-white">
                                    @include('guide.partials.block', ['blocks' => $section['blocks'] ?? []])
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-white p-16 text-center">
                                <i class="fas fa-book-open text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 font-medium">No articles for this role.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var search = document.getElementById('guide-search');
    var clearBtn = document.getElementById('guide-search-clear');
    var statusEl = document.getElementById('guide-search-status');
    var articles = document.querySelectorAll('.docs-article');
    var tocLinks = document.querySelectorAll('.docs-toc-link');
    var jumpLinks = document.querySelectorAll('.docs-jump-link');
    var refItems = document.querySelectorAll('.docs-ref-item');
    var tocOpen = document.getElementById('docs-toc-open');
    var mobileToc = document.getElementById('docs-mobile-toc');
    var backdrop = document.getElementById('docs-toc-backdrop');
    var searchForm = document.getElementById('guide-search-form');
    var progressFill = document.getElementById('docs-sidebar-progress');
    var mobileProgressFill = document.getElementById('docs-mobile-progress');
    var backTop = document.getElementById('docs-back-top');
    var backTopMobile = document.getElementById('docs-back-top-mobile');
    var totalArticles = articles.length;
    var activeArticleIndex = 0;

    function updateProgress(index) {
        if (totalArticles === 0) return;
        activeArticleIndex = index;
        var pct = Math.round(((index + 1) / totalArticles) * 100);
        if (progressFill) progressFill.style.width = pct + '%';
        if (mobileProgressFill) mobileProgressFill.style.width = pct + '%';
    }

    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        closeMobileToc();
    }

    function scrollToTarget(el) {
        if (!el) return;
        var top = el.getBoundingClientRect().top + window.scrollY - 96;
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    }

    function flashTarget(el) {
        if (!el) return;
        el.classList.remove('docs-search-flash');
        void el.offsetWidth;
        el.classList.add('docs-search-flash');
        setTimeout(function () { el.classList.remove('docs-search-flash'); }, 2200);
    }

    function setActiveNav(sectionId) {
        if (!sectionId) return;
        var hash = '#' + sectionId;
        tocLinks.forEach(function (a) {
            var href = a.getAttribute('href') || '';
            if (href.indexOf('#reference-') === 0) return;
            a.classList.toggle('is-active', href === hash);
        });
        jumpLinks.forEach(function (a) {
            a.classList.toggle('is-active', a.getAttribute('href') === hash);
        });
    }

    function findBestArticle(q) {
        var best = null;
        var bestScore = -1;
        articles.forEach(function (el) {
            var blob = el.getAttribute('data-guide-search') || '';
            var title = (el.querySelector('h2') && el.querySelector('h2').textContent || '').toLowerCase();
            var score = -1;
            if (title.indexOf(q) === 0) score = 100;
            else if (title.indexOf(q) !== -1) score = 80;
            else if ((el.id || '').indexOf(q) !== -1) score = 60;
            else if (blob.indexOf(q) !== -1) score = 40;
            if (score > bestScore) {
                bestScore = score;
                best = el;
            }
        });
        return best;
    }

    function findRefMatch(q) {
        var match = null;
        refItems.forEach(function (el) {
            var blob = (el.getAttribute('data-guide-search') || '').toLowerCase();
            if (!match && blob.indexOf(q) !== -1) match = el;
        });
        return match ? match.closest('.docs-ref-card') : null;
    }

    function runSearch() {
        var q = (search && search.value || '').trim().toLowerCase();
        if (!q) {
            if (statusEl) statusEl.textContent = 'Enter a keyword to jump to a section';
            return;
        }

        var article = findBestArticle(q);
        if (article) {
            var title = article.querySelector('h2');
            var idx = parseInt(article.getAttribute('data-article-index') || '0', 10);
            scrollToTarget(article);
            flashTarget(article);
            setActiveNav(article.id);
            updateProgress(idx);
            if (statusEl) statusEl.textContent = 'Jumped to: ' + (title ? title.textContent.trim() : 'section');
            if (clearBtn) clearBtn.classList.remove('hidden');
            return;
        }

        var refCard = findRefMatch(q);
        if (refCard) {
            scrollToTarget(refCard);
            flashTarget(refCard);
            if (statusEl) statusEl.textContent = 'Jumped to glossary / timelines';
            if (clearBtn) clearBtn.classList.remove('hidden');
            return;
        }

        if (statusEl) statusEl.textContent = 'No section found — try another keyword';
    }

    function resetSearch() {
        if (search) search.value = '';
        if (clearBtn) clearBtn.classList.add('hidden');
        if (statusEl) statusEl.textContent = '';
        articles.forEach(function (el) { el.classList.remove('docs-search-flash'); });
        document.querySelectorAll('.docs-ref-card').forEach(function (el) { el.classList.remove('docs-search-flash'); });
        search && search.focus();
    }

    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            runSearch();
        });
    }
    if (search) {
        search.addEventListener('input', function () {
            if (clearBtn) clearBtn.classList.toggle('hidden', !search.value);
        });
    }
    if (clearBtn) {
        clearBtn.addEventListener('click', resetSearch);
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var id = entry.target.id;
                var idx = parseInt(entry.target.getAttribute('data-article-index') || '0', 10);
                tocLinks.forEach(function (a) {
                    var href = a.getAttribute('href') || '';
                    if (href.indexOf('#reference-') === 0) return;
                    a.classList.toggle('is-active', href === '#' + id);
                });
                jumpLinks.forEach(function (a) {
                    a.classList.toggle('is-active', a.getAttribute('href') === '#' + id);
                });
                updateProgress(idx);
            }
        });
    }, { rootMargin: '-20% 0px -70% 0px', threshold: 0 });
    articles.forEach(function (a) { observer.observe(a); });
    if (totalArticles > 0) updateProgress(0);

    backTop && backTop.addEventListener('click', scrollToTop);
    backTopMobile && backTopMobile.addEventListener('click', scrollToTop);

    function closeMobileToc() {
        mobileToc && mobileToc.classList.remove('open');
        backdrop && backdrop.classList.add('hidden');
    }
    tocOpen && tocOpen.addEventListener('click', function () {
        mobileToc && mobileToc.classList.add('open');
        backdrop && backdrop.classList.remove('hidden');
    });
    backdrop && backdrop.addEventListener('click', closeMobileToc);
    tocLinks.forEach(function (a) {
        a.addEventListener('click', function () {
            closeMobileToc();
        });
    });
});
</script>
@endpush
