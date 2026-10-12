<!doctype html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'Dashboard') - Slipin Coffee</title>
    @include('layouts.head')
</head>
<body class="min-h-screen bg-neutral-950 text-neutral-100 antialiased">
@php
    $navGroups = [
        ['label' => 'UTAMA', 'items' => [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'pattern' => 'dashboard', 'icon' => 'dashboard'],
        ]],
        ['label' => 'OPERASIONAL', 'items' => [
            ['route' => 'sesi.index', 'label' => 'Sesi Jualan', 'pattern' => 'sesi*', 'icon' => 'calendar'],
            ['route' => 'pengeluaran.index', 'label' => 'Pengeluaran', 'pattern' => 'pengeluaran*', 'icon' => 'wallet'],
            ['route' => 'polycup.index', 'label' => 'Stok Polycup', 'pattern' => 'polycup*', 'icon' => 'box'],
        ]],
        ['label' => 'LAPORAN & DATA', 'items' => [
            ['route' => 'laporan.index', 'label' => 'Laporan', 'pattern' => 'laporan*', 'icon' => 'chart', 'ownerOnly' => true],
            ['route' => 'master.index', 'label' => 'Master Data', 'pattern' => 'master*', 'icon' => 'database', 'ownerOnly' => true],
            ['route' => 'user.index', 'label' => 'Pengguna', 'pattern' => 'user*', 'icon' => 'users', 'ownerOnly' => true],
        ]],
    ];
    $me = auth()->user();
@endphp

<div id="sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-black/60 lg:hidden" aria-hidden="true"></div>
<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-zinc-800 bg-zinc-950 transition-transform duration-200 lg:translate-x-0"
       style="padding-top: env(safe-area-inset-top, 0px); padding-bottom: env(safe-area-inset-bottom, 0px)"
       aria-label="Navigasi utama">
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-zinc-800 px-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 font-semibold">
            <img src="/icons/logo-mark.png" class="h-9 w-auto" alt="">
            <span class="text-lg font-extrabold tracking-tight text-brand-200">Slipin Coffee</span>
        </a>
        <button id="sidebar-close" type="button" class="rounded-lg p-2 text-neutral-400 hover:bg-neutral-800 hover:text-white lg:hidden" aria-label="Tutup menu">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
    </div>

    <div class="border-b border-zinc-800 p-3">
        <label for="menu-search" class="sr-only">Cari menu</label>
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m16 16 4 4"/>
            </svg>
            <input id="menu-search" type="search" placeholder="Cari menu..." autocomplete="off"
                   class="w-full rounded-lg border border-zinc-700 bg-zinc-900 py-2 pl-9 pr-3 text-sm text-zinc-100 placeholder-zinc-500 outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Menu aplikasi">
        @foreach ($navGroups as $group)
            @php
                $visibleItems = array_filter($group['items'], fn ($item) => ! ($item['ownerOnly'] ?? false) || $me->isOwner());
            @endphp
            @if (count($visibleItems))
                <section class="menu-group mb-5 last:mb-0">
                    <h2 class="mb-2 px-2 text-[11px] font-semibold tracking-wider text-zinc-500">{{ $group['label'] }}</h2>
                    <div class="space-y-1">
                        @foreach ($visibleItems as $item)
                            @php $active = request()->routeIs($item['pattern']); @endphp
                            <a href="{{ route($item['route']) }}" data-menu-item data-menu-label="{{ strtolower($item['label']) }}"
                               @if ($active) aria-current="page" @endif
                               class="flex items-center gap-3 rounded-lg px-2.5 py-2.5 text-sm transition {{ $active ? 'bg-brand-950 font-medium text-brand-200 shadow-sm ring-1 ring-brand-800' : 'text-zinc-300 hover:bg-zinc-900 hover:text-white' }}">
                                <svg class="h-[18px] w-[18px] shrink-0 {{ $active ? 'text-brand-300' : 'text-zinc-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    @switch($item['icon'])
                                        @case('dashboard')
                                            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
                                            @break
                                        @case('calendar')
                                            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/><path d="m9 15 2 2 4-4"/>
                                            @break
                                        @case('wallet')
                                            <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4H20v16H5a2 2 0 0 1-2-2V6.5A2.5 2.5 0 0 1 5.5 4"/><path d="M16 13h4M4 8h16"/><circle cx="16" cy="13" r=".5" fill="currentColor"/>
                                            @break
                                        @case('box')
                                            <path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="M3 8v8l9 5 9-5V8M12 13v8"/>
                                            @break
                                        @case('chart')
                                            <path d="M4 19V5M4 19h17"/><path d="m7 15 4-4 3 2 6-7"/><path d="M17 6h3v3"/>
                                            @break
                                        @case('database')
                                            <ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>
                                            @break
                                        @case('users')
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                                            @break
                                    @endswitch
                                </svg>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
        <p id="menu-empty" class="hidden px-2 py-4 text-sm text-zinc-500">Menu tidak ditemukan.</p>
    </nav>

    <div class="shrink-0 border-t border-zinc-800 p-3">
        <div class="mb-3 flex items-center gap-3 px-2">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-zinc-800 text-sm font-semibold text-zinc-200">
                {{ mb_strtoupper(mb_substr($me->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-medium text-zinc-100">{{ $me->name }}</div>
                <div class="truncate text-xs text-zinc-500">{{ $me->role }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-sm text-zinc-400 transition hover:bg-zinc-900 hover:text-white">
                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>
                Keluar
            </button>
        </form>
    </div>
</aside>

<div class="min-h-screen lg:pl-64">
    <header class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-zinc-800 bg-zinc-950/95 px-4 backdrop-blur sm:px-6"
            style="padding-top: env(safe-area-inset-top, 0px)">
        <div class="flex items-center gap-3">
            <button id="sidebar-open" type="button" class="rounded-lg p-2 text-zinc-300 hover:bg-zinc-800 lg:hidden" aria-label="Buka menu" aria-expanded="false" aria-controls="app-sidebar">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <span class="text-sm font-medium text-zinc-300">@yield('title', 'Dashboard')</span>
        </div>
        <span class="hidden text-sm text-zinc-500 sm:inline">{{ $me->name }}</span>
    </header>

<main class="mx-auto max-w-6xl space-y-4 px-4 py-5 sm:px-6" style="padding-bottom: calc(2rem + env(safe-area-inset-bottom, 0px))">
    @if (session('ok'))
        <div class="rounded-lg border border-emerald-800 bg-emerald-950 px-4 py-3 text-sm text-emerald-300">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-lg border border-red-900 bg-red-950 px-4 py-3 text-sm text-red-300">
            @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    @yield('content')
</main>
</div>

@stack('scripts')
<script>
    (() => {
        const sidebar = document.getElementById('app-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const openButton = document.getElementById('sidebar-open');
        const closeButton = document.getElementById('sidebar-close');
        const search = document.getElementById('menu-search');
        const emptyMessage = document.getElementById('menu-empty');

        const setSidebarOpen = (isOpen) => {
            sidebar.classList.toggle('-translate-x-full', !isOpen);
            backdrop.classList.toggle('hidden', !isOpen);
            openButton.setAttribute('aria-expanded', String(isOpen));
        };

        openButton.addEventListener('click', () => setSidebarOpen(true));
        closeButton.addEventListener('click', () => setSidebarOpen(false));
        backdrop.addEventListener('click', () => setSidebarOpen(false));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setSidebarOpen(false);
        });

        search.addEventListener('input', () => {
            const query = search.value.trim().toLocaleLowerCase();
            let visibleCount = 0;

            document.querySelectorAll('.menu-group').forEach((group) => {
                const items = group.querySelectorAll('[data-menu-item]');
                let groupVisibleCount = 0;

                items.forEach((item) => {
                    const visible = item.dataset.menuLabel.includes(query);
                    item.classList.toggle('hidden', !visible);
                    groupVisibleCount += Number(visible);
                });

                group.classList.toggle('hidden', groupVisibleCount === 0);
                visibleCount += groupVisibleCount;
            });

            emptyMessage.classList.toggle('hidden', visibleCount !== 0);
        });
    })();
</script>
</body>
</html>
