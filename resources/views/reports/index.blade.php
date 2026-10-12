@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
@use('App\Support\Fmt')
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-xl font-semibold">Laporan Bulanan</h1>
    <div class="flex items-center gap-2">
        <form method="GET"><input type="month" name="bulan" value="{{ $bulan }}" class="input !w-auto" onchange="this.form.submit()"></form>
        <a href="{{ route('laporan.export', ['bulan' => $bulan]) }}" class="btn btn-ghost">Export CSV</a>
    </div>
</div>

<div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
    <div class="card"><div class="stat-l">Total Sales</div><div class="stat">{{ Fmt::rp($totals->sales) }}</div></div>
    <div class="card"><div class="stat-l">Cash</div><div class="stat">{{ Fmt::rp($totals->cash) }}</div></div>
    <div class="card"><div class="stat-l">QRIS</div><div class="stat">{{ Fmt::rp($totals->qris) }}</div></div>
    <div class="card"><div class="stat-l">Pengeluaran</div><div class="stat">{{ Fmt::rp($totals->expense) }}</div></div>
    <div class="card"><div class="stat-l">Profit</div><div class="stat {{ $totals->profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ Fmt::rp($totals->profit) }}</div></div>
</div>
<p class="hint">Profit = total sales − seluruh pengeluaran bulan ini (termasuk belanja bahan &amp; restock polycup), jadi basisnya arus kas.</p>

@if ($check->sesi > 0)
<div class="card">
    <div class="mb-2 text-sm font-medium">Akurasi pencatatan ({{ $check->sesi }} sesi selesai)</div>
    <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
        <div>Selisih uang total: <span class="font-medium {{ Fmt::selClass($check->uang) }}">{{ (int) $check->uang === 0 ? 'Pas ✓' : Fmt::selRp($check->uang) }}</span></div>
        <div>Selisih polycup total: <span class="font-medium {{ Fmt::selClass($check->polycup) }}">{{ (int) $check->polycup === 0 ? 'Pas ✓' : Fmt::sel($check->polycup) . ' cup' }}</span></div>
    </div>
</div>
@endif

<div class="card !p-0 overflow-x-auto">
    <div class="px-4 pt-4 text-sm font-medium">Harian</div>
    <table class="mt-2 w-full"><thead class="border-b border-neutral-800"><tr>
        <th class="th">Tanggal</th><th class="th text-right">Sales</th><th class="th text-right">Cash</th><th class="th text-right">QRIS</th>
        <th class="th text-right">Pengeluaran</th><th class="th text-right">Profit</th></tr></thead>
        <tbody class="divide-y divide-neutral-800">
        @forelse ($daily as $d)
            <tr><td class="td">{{ Fmt::tgl($d->report_date, 'D, d M') }}</td>
                <td class="td text-right">{{ Fmt::rp($d->total_sales) }}</td>
                <td class="td text-right text-neutral-400">{{ Fmt::rp($d->cash_masuk) }}</td>
                <td class="td text-right text-neutral-400">{{ Fmt::rp($d->qris_masuk) }}</td>
                <td class="td text-right">{{ Fmt::rp($d->total_pengeluaran) }}</td>
                <td class="td text-right font-medium {{ $d->profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ Fmt::rp($d->profit) }}</td></tr>
        @empty
            <tr><td colspan="6" class="td py-6 text-center text-neutral-500">Belum ada data.</td></tr>
        @endforelse
        </tbody></table>
</div>

<div class="grid gap-4 lg:grid-cols-3">
    <div class="card">
        <div class="mb-2 text-sm font-medium">Menu terlaris</div>
        @forelse ($products as $p)
            <div class="flex justify-between py-0.5 text-sm"><span>{{ $p->name }} <span class="text-neutral-500">×{{ Fmt::num($p->qty) }}</span></span><span>{{ Fmt::rp($p->omzet) }}</span></div>
        @empty <p class="text-sm text-neutral-500">-</p> @endforelse
    </div>
    <div class="card">
        <div class="mb-2 text-sm font-medium">Pengeluaran per kategori</div>
        @forelse ($expenseByCat as $c)
            <div class="flex justify-between py-0.5 text-sm"><span class="text-neutral-400">{{ $c->name }}</span><span>{{ Fmt::rp($c->total) }}</span></div>
        @empty <p class="text-sm text-neutral-500">-</p> @endforelse
    </div>
    <div class="card">
        <div class="mb-2 text-sm font-medium">Per lokasi</div>
        @forelse ($byLocation as $l)
            <div class="py-1 text-sm"><div class="flex justify-between"><span>{{ $l->location }}</span><span>{{ Fmt::rp($l->sales) }}</span></div>
                <div class="text-xs text-neutral-500">{{ $l->sesi }} sesi · {{ Fmt::num($l->cup) }} cup · rata-rata {{ Fmt::rp($l->rata) }}/sesi</div></div>
        @empty <p class="text-sm text-neutral-500">-</p> @endforelse
    </div>
</div>
@endsection
