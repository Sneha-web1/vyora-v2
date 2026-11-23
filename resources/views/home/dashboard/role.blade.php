@extends('layout.master')
@section('title', $title ?? 'Dashboard')

@section('main-section')
    <x-alert-modal :type="session('success') ? 'success' : (session('error') ? 'error' : '')" :message="session('success') ?? session('error')" />

    <style>
        .rd .welcome { background:#fff; border:1px solid #e6e7ee; border-radius:8px; padding:26px 28px; display:flex; align-items:center; gap:18px; margin-bottom:20px; }
        .rd .av { width:56px; height:56px; border-radius:50%; background:#ebeaf0; color:#2d2765; font-size:22px; font-weight:700; display:flex; align-items:center; justify-content:center; }
        .rd .rolebadge { display:inline-block; background:#ebeaf0; color:#2d2765; font-size:12px; font-weight:600; border-radius:20px; padding:3px 12px; }
        .rd .mcard { background:#fff; border:1px solid #e6e7ee; border-radius:8px; padding:18px 20px; height:100%; }
        .rd .mcard h6 { font-weight:700; color:#1f2430; margin-bottom:10px; }
        .rd .mcard h6 i { color:#2d2765; margin-right:6px; }
        .rd .mcard a { display:block; padding:6px 0; font-size:14px; color:#4b5063; text-decoration:none; border-bottom:1px solid #f0f1f5; }
        .rd .mcard a:last-child { border-bottom:0; }
        .rd .mcard a:hover { color:#2d2765; font-weight:600; }
    </style>

    <div class="page-wrapper rd">
        <div class="content container-fluid">
            <div class="welcome">
                <div class="av">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                <div>
                    <h4 class="mb-1" style="font-weight:700;color:#1f2430;">Welcome, {{ auth()->user()->name }}</h4>
                    <span class="rolebadge">{{ $roleName }}</span>
                </div>
            </div>

            @php
                // a card per top-level menu; leaf menus without a page yet ('#') are skipped
                $flat = function ($nodes) use (&$flat) {
                    $out = [];
                    foreach ($nodes as $n) {
                        if (!empty($n['children'])) { $out = array_merge($out, $flat($n['children'])); }
                        elseif (!empty($n['route']) && $n['route'] !== '#') { $out[] = $n; }
                    }
                    return $out;
                };
            @endphp

            @if (count($menus))
                <div class="row g-3">
                    @foreach ($menus as $top)
                        @php $links = !empty($top['children']) ? $flat($top['children']) : ($top['route'] && $top['route'] !== '#' ? [$top] : []); @endphp
                        @if (count($links))
                            <div class="col-md-4">
                                <div class="mcard">
                                    <h6><i class="{{ $top['icon'] ?: 'ti ti-point' }}"></i>{{ $top['title'] }}</h6>
                                    @foreach ($links as $l)
                                        <a href="{{ \App\Support\MenuRegistry::url($l['route']) }}">{{ $l['title'] }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="mcard text-center text-muted" style="padding:40px;">
                    <i class="ti ti-lock" style="font-size:28px;"></i>
                    <p class="mb-0 mt-2">No menus have been assigned to your role yet. Please contact the administrator.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
