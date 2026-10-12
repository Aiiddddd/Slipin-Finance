@extends('layouts.app')
@section('title', 'Stok Polycup')
@section('content')
@use('App\Support\Fmt')
@php $owner = auth()->user()->isOwner(); @endphp
<h1 class="text-xl font-semibold">Stok Polycup</h1>

<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="card"><div class="stat-l">Total Dibeli</div><div class="stat">{{ Fmt::num($stock->total_dibeli) }}</div></div>
    <div class="card"><div class="stat-l">Dibawa Jualan</div><div class="stat">{{ Fmt::num($stock->total_dibawa_jualan) }}</div></div>
    <div class="card"><div class="stat-l">Sisa Balik</div><div class="stat">{{ Fmt::num($stock->total_sisa_balik) }}</div></div>
    <div class="card border-neutral-500"><div class="stat-l">Estimasi Stok Gudang</div><div class="stat text-white">{{ Fmt::num($stock->estimasi_stok_gudang) }}</div></div>
</div>
<p class="hint">Estimasi = total dibeli − dibawa jualan + sisa yang dibawa pulang. Kalau kamu sudah punya stok sebelum pakai sistem ini, catat sebagai "pembelian" dengan catatan <i>Stok awal</i> (harga 0).</p>

@if ($owner)
<form method="POST" action="{{ route('polycup.store') }}" class="card space-y-3">
    @csrf
    <div class="text-sm font-medium">Catat pembelian / restock</div>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div><label class="label">Tanggal</label><input type="date" name="purchase_date" value="{{ now()->toDateString() }}" class="input" required></div>
        <div><label class="label">Jumlah (pcs)</label><input type="number" min="1" name="qty" class="input" required></div>
        <div><label class="label">Total harga</label><input type="number" min="0" name="total_price" class="input" value="0"></div>
        <div><label class="label">Supplier</label><input name="supplier" class="input"></div>
    </div>
    <div class="grid gap-3 lg:grid-cols-2">
        <input name="notes" class="input" placeholder="Catatan (mis. Stok awal)">
        <label class="flex items-center gap-2 text-sm text-neutral-300">
            <input type="checkbox" name="catat_pengeluaran" value="1" checked class="h-4 w-4 rounded"> Catat juga sebagai pengeluaran (kategori Polycup &amp; Packaging)
        </label>
    </div>
    <button class="btn btn-primary">Simpan</button>
</form>
@endif

<div class="grid gap-4 lg:grid-cols-2">
    <div class="card !p-0 overflow-x-auto">
        <div class="px-4 pt-4 text-sm font-medium">Riwayat pembelian</div>
        <table class="mt-2 w-full"><thead class="border-b border-neutral-800"><tr>
            <th class="th">Tanggal</th><th class="th text-right">Qty</th><th class="th text-right">Harga</th><th class="th text-right">/pcs</th><th class="th"></th></tr></thead>
            <tbody class="divide-y divide-neutral-800">
            @forelse ($purchases as $p)
                <tr><td class="td">{{ Fmt::tgl($p->purchase_date, 'd M Y') }}<div class="text-xs text-neutral-500">{{ $p->supplier }} {{ $p->notes }}</div></td>
                    <td class="td text-right">{{ Fmt::num($p->qty) }}</td>
                    <td class="td text-right">{{ Fmt::rp($p->total_price) }}</td>
                    <td class="td text-right text-neutral-400">{{ $p->total_price > 0 ? Fmt::rp($p->total_price / $p->qty) : '-' }}</td>
                    <td class="td text-right">@if ($owner)<form method="POST" action="{{ route('polycup.destroy', $p->id) }}" onsubmit="return confirm('Hapus pembelian ini (termasuk pengeluaran terkait)?')">@csrf @method('DELETE')<button class="text-red-400 underline">hapus</button></form>@endif</td></tr>
            @empty
                <tr><td colspan="5" class="td py-6 text-center text-neutral-500">Belum ada pembelian.</td></tr>
            @endforelse
            </tbody></table>
        <div class="px-4 pb-3">{{ $purchases->links() }}</div>
    </div>

    <div class="card !p-0 overflow-x-auto">
        <div class="px-4 pt-4 text-sm font-medium">Pemakaian 10 sesi terakhir</div>
        <table class="mt-2 w-full"><thead class="border-b border-neutral-800"><tr>
            <th class="th">Tanggal</th><th class="th text-right">Bawa</th><th class="th text-right">Terjual</th><th class="th text-right">Rusak</th><th class="th text-right">Sisa</th></tr></thead>
            <tbody class="divide-y divide-neutral-800">
            @forelse ($usage as $u)
                <tr><td class="td"><a class="underline" href="{{ route('sesi.show', $u->session_id) }}">{{ Fmt::tgl($u->sales_date, 'd M') }}</a></td>
                    <td class="td text-right">{{ $u->polycup_awal + $u->polycup_tambah }}</td>
                    <td class="td text-right">{{ $u->cup_terjual }}</td>
                    <td class="td text-right">{{ $u->polycup_rusak }}</td>
                    <td class="td text-right">{{ $u->status === 'closed' ? $u->sisa_fisik : '-' }}
                        @if ($u->status === 'closed' && (int) $u->selisih_polycup !== 0)<span class="text-xs text-red-400">({{ Fmt::sel($u->selisih_polycup) }})</span>@endif</td></tr>
            @empty
                <tr><td colspan="5" class="td py-6 text-center text-neutral-500">Belum ada sesi.</td></tr>
            @endforelse
            </tbody></table>
    </div>
</div>
@endsection
