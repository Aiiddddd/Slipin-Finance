@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@use('App\Support\Fmt')
@php $owner = auth()->user()->isOwner(); $max = max(1, collect($week)->max('sales')); @endphp

<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold">Halo, {{ auth()->user()->name }} ☕</h1>
        <p class="text-sm text-neutral-400">{{ Fmt::tgl(now(), 'l, d F Y') }}</p>
    </div>
    <a href="{{ route('sesi.create') }}" class="btn btn-primary">+ Buka Sesi Jualan</a>
</div>

@if ($openSessions->count())
    <div class="card border-lime-700/60 bg-lime-950/40">
        <div class="mb-2 text-sm font-medium text-lime-300">Sesi yang masih berjalan</div>
        <div class="space-y-2">
            @foreach ($openSessions as $o)
                <a href="{{ route('sesi.show', $o->session_id) }}" class="flex items-center justify-between rounded-lg bg-neutral-900 px-3 py-2 text-sm hover:bg-neutral-800">
                    <span>{{ Fmt::tgl($o->sales_date) }} · {{ $o->location ?? 'Tanpa lokasi' }}</span>
                    <span class="text-neutral-400">{{ Fmt::num($o->cup_terjual) }} cup · {{ Fmt::rp($o->total_sales) }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif

<div>
    <div class="mb-2 text-sm font-medium text-neutral-400">Hari ini</div>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <div class="card"><div class="stat-l">Total Sales</div><div class="stat">{{ Fmt::rp($t->sales) }}</div></div>
        <div class="card"><div class="stat-l">Cup Terjual</div><div class="stat">{{ Fmt::num($t->cup) }}</div></div>
        <div class="card"><div class="stat-l">Cash</div><div class="stat">{{ Fmt::rp($t->cash) }}</div></div>
        <div class="card"><div class="stat-l">QRIS</div><div class="stat">{{ Fmt::rp($t->qris) }}</div></div>
        <div class="card"><div class="stat-l">Pengeluaran</div><div class="stat">{{ Fmt::rp($expToday) }}</div></div>
    </div>
</div>

<div>
    <div class="mb-2 text-sm font-medium text-neutral-400">Bulan ini</div>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="card"><div class="stat-l">Omzet</div><div class="stat">{{ Fmt::rp($m->sales) }}</div></div>
        @if ($owner)
            <div class="card"><div class="stat-l">Pengeluaran</div><div class="stat">{{ Fmt::rp($m->expense) }}</div></div>
            <div class="card"><div class="stat-l">Profit</div>
                <div class="stat {{ $m->profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ Fmt::rp($m->profit) }}</div></div>
        @endif
        <div class="card"><div class="stat-l">Stok Polycup (gudang)</div><div class="stat">{{ Fmt::num($stock->estimasi_stok_gudang) }}</div>
            <a href="{{ route('polycup.index') }}" class="hint underline">lihat detail</a></div>
    </div>
</div>

<div class="grid gap-4 lg:grid-cols-2">
    <div class="card">
        <div class="mb-3 text-sm font-medium">Penjualan 7 hari terakhir</div>
        <div class="flex h-40 items-end gap-2">
            @foreach ($week as $w)
                <div class="flex flex-1 flex-col items-center justify-end gap-1">
                    <div class="text-[10px] text-neutral-400">{{ $w['sales'] > 0 ? Fmt::num($w['sales'] / 1000) . 'k' : '' }}</div>
                    <div class="w-full rounded-t bg-neutral-300" style="height: {{ max(2, round($w['sales'] / $max * 100)) }}px"></div>
                    <div class="text-[10px] text-neutral-500">{{ $w['tgl'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="mb-3 flex items-center justify-between text-sm font-medium">
            <span>Sesi terakhir</span><a href="{{ route('sesi.index') }}" class="text-xs text-neutral-400 underline">semua</a>
        </div>
        @forelse ($recent as $x)
            <a href="{{ route('sesi.show', $x->session_id) }}" class="flex items-center justify-between border-b border-neutral-800 py-2 text-sm last:border-0 hover:text-white">
                <span>{{ Fmt::tgl($x->sales_date, 'd M') }} · {{ $x->location ?? '-' }}</span>
                <span class="text-right">{{ Fmt::rp($x->total_sales) }}
                    <span class="ml-1 text-xs {{ Fmt::selClass($x->selisih_uang) }}">{{ (int) $x->selisih_uang === 0 ? '✓' : Fmt::selRp($x->selisih_uang) }}</span></span>
            </a>
        @empty
            <p class="text-sm text-neutral-500">Belum ada sesi yang ditutup.</p>
        @endforelse
    </div>
</div>
@endsection
