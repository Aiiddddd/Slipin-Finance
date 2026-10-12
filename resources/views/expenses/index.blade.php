@extends('layouts.app')
@section('title', 'Pengeluaran')
@section('content')
@use('App\Support\Fmt')
@php $owner = auth()->user()->isOwner(); @endphp
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-xl font-semibold">Pengeluaran</h1>
    <div class="flex flex-wrap items-center gap-2">
        <form method="GET" class="flex items-center gap-2">
            <input type="month" name="bulan" value="{{ $bulan }}" class="input !w-auto" onchange="this.form.submit()">
            <select name="kategori" class="input !w-auto" onchange="this.form.submit()">
                <option value="">Semua kategori</option>
                @foreach ($categories as $c)<option value="{{ $c->id }}" @selected($cat == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </form>
        <a href="{{ route('pengeluaran.create') }}" class="btn btn-primary">+ Catat</a>
    </div>
</div>

<div class="grid gap-3 lg:grid-cols-3">
    <div class="card">
        <div class="stat-l">Total {{ $cat ? '(kategori terpilih)' : 'bulan ini' }}</div>
        <div class="stat">{{ Fmt::rp($total) }}</div>
    </div>
    <div class="card lg:col-span-2">
        <div class="mb-2 text-sm font-medium">Per kategori</div>
        @forelse ($byCat as $b)
            <div class="flex justify-between py-0.5 text-sm"><span class="text-neutral-400">{{ $b->name }}</span><span>{{ Fmt::rp($b->total) }}</span></div>
        @empty
            <p class="text-sm text-neutral-500">Belum ada pengeluaran.</p>
        @endforelse
    </div>
</div>

<div class="card !p-0 overflow-x-auto">
    <table class="w-full">
        <thead class="border-b border-neutral-800"><tr>
            <th class="th">Tanggal</th><th class="th">Kategori</th><th class="th">Keterangan</th>
            <th class="th text-right">Jumlah</th><th class="th">Bayar</th><th class="th">Dana</th><th class="th">Nota</th><th class="th"></th>
        </tr></thead>
        <tbody class="divide-y divide-neutral-800">
        @forelse ($expenses as $e)
            <tr>
                <td class="td">{{ Fmt::tgl($e->expense_date, 'd M') }}</td>
                <td class="td text-neutral-400">{{ $e->category->name ?? '-' }}</td>
                <td class="td whitespace-normal">{{ $e->description }}
                    @if ($e->qty)<span class="text-xs text-neutral-500">({{ rtrim(rtrim(number_format($e->qty, 2, ',', '.'), '0'), ',') }} {{ $e->unit }})</span>@endif
                    @if ($e->supplier)<div class="text-xs text-neutral-500">{{ $e->supplier }}</div>@endif</td>
                <td class="td text-right font-medium">{{ Fmt::rp($e->amount) }}</td>
                <td class="td uppercase text-neutral-400">{{ $e->payment_method }}</td>
                <td class="td text-neutral-400">{{ $e->paid_from === 'kas_jualan' ? 'Kas jualan' : 'Modal owner' }}</td>
                <td class="td">@if ($e->receipt_path)<a class="underline" target="_blank" href="{{ asset('storage/' . $e->receipt_path) }}">lihat</a>@else<span class="text-neutral-600">-</span>@endif</td>
                <td class="td text-right">
                    <a href="{{ route('pengeluaran.edit', $e->id) }}" class="text-neutral-300 underline">edit</a>
                    @if ($owner)
                        <form method="POST" action="{{ route('pengeluaran.destroy', $e->id) }}" class="ml-2 inline" onsubmit="return confirm('Hapus pengeluaran ini?')">
                            @csrf @method('DELETE')<button class="text-red-400 underline">hapus</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="td py-8 text-center text-neutral-500">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $expenses->links() }}
@endsection
