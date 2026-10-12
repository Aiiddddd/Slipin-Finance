@extends('layouts.app')
@section('title', 'Sesi ' . \App\Support\Fmt::tgl($s->sales_date, 'd M Y'))
@section('content')
@use('App\Support\Fmt')
@php $open = $s->status === 'open'; $owner = auth()->user()->isOwner(); @endphp

<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <a href="{{ route('sesi.index') }}" class="text-xs text-neutral-400 underline">← semua sesi</a>
        <h1 class="text-xl font-semibold">{{ Fmt::tgl($s->sales_date, 'l, d F Y') }}</h1>
        <p class="text-sm text-neutral-400">
            {{ $sum->location ?? 'Tanpa lokasi' }} · {{ $sum->crew ?? '-' }} ·
            <span class="badge {{ $open ? 'bg-lime-900 text-lime-300' : 'bg-neutral-800 text-neutral-300' }}">{{ $open ? 'Berjalan' : 'Selesai' }}</span>
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if ($open)
            <a href="{{ route('sesi.closeForm', $s->id) }}" class="btn btn-primary">Tutup Sesi</a>
        @elseif ($owner)
            <form method="POST" action="{{ route('sesi.reopen', $s->id) }}">@csrf<button class="btn btn-ghost">Buka lagi</button></form>
        @endif
        @if ($owner)
            <form method="POST" action="{{ route('sesi.destroy', $s->id) }}" onsubmit="return confirm('Hapus sesi ini beserta data penjualannya?')">
                @csrf @method('DELETE')<button class="btn btn-danger">Hapus</button>
            </form>
        @endif
    </div>
</div>

{{-- Ringkasan --}}
<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="card"><div class="stat-l">Total Sales</div><div class="stat" id="sum-sales">{{ Fmt::rp($sum->total_sales) }}</div></div>
    <div class="card"><div class="stat-l">Cup Terjual</div><div class="stat" id="sum-cups">{{ Fmt::num($sum->cup_terjual) }}</div></div>
    <div class="card"><div class="stat-l">Polycup Awal</div><div class="stat">{{ Fmt::num($s->polycup_awal + $s->polycup_tambah) }}</div>
        <div class="hint">awal {{ $s->polycup_awal }} + tambah {{ $s->polycup_tambah }}</div></div>
    <div class="card"><div class="stat-l">Sisa Polycup (seharusnya)</div><div class="stat">{{ Fmt::num($sum->sisa_seharusnya) }}</div>
        <div class="hint">rusak/gratis: {{ $s->polycup_rusak }}</div></div>
</div>

@unless ($open)
    <div class="grid gap-3 sm:grid-cols-2">
        <div class="card">
            <div class="mb-2 text-sm font-medium">Rekonsiliasi uang</div>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-neutral-400">Total sales (catatan)</dt><dd>{{ Fmt::rp($sum->total_sales) }}</dd></div>
                <div class="flex justify-between"><dt class="text-neutral-400">Cash masuk</dt><dd>{{ Fmt::rp($s->cash_masuk) }}</dd></div>
                <div class="flex justify-between"><dt class="text-neutral-400">QRIS masuk</dt><dd>{{ Fmt::rp($s->qris_masuk) }}</dd></div>
                <div class="flex justify-between border-t border-neutral-800 pt-1 font-medium"><dt>Total uang masuk</dt><dd>{{ Fmt::rp($sum->uang_masuk) }}</dd></div>
                <div class="flex justify-between font-medium {{ Fmt::selClass($sum->selisih_uang) }}"><dt>Selisih</dt>
                    <dd>{{ (int) $sum->selisih_uang === 0 ? 'Pas ✓' : Fmt::selRp($sum->selisih_uang) }}</dd></div>
            </dl>
        </div>
        <div class="card">
            <div class="mb-2 text-sm font-medium">Rekonsiliasi polycup</div>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-neutral-400">Sisa seharusnya</dt><dd>{{ Fmt::num($sum->sisa_seharusnya) }}</dd></div>
                <div class="flex justify-between"><dt class="text-neutral-400">Sisa fisik (hasil hitung)</dt><dd>{{ Fmt::num($s->polycup_sisa) }}</dd></div>
                <div class="flex justify-between border-t border-neutral-800 pt-1 font-medium {{ Fmt::selClass($sum->selisih_polycup) }}"><dt>Selisih</dt>
                    <dd>{{ (int) $sum->selisih_polycup === 0 ? 'Pas ✓' : Fmt::sel($sum->selisih_polycup) . ' cup' }}</dd></div>
            </dl>
            <p class="hint">Minus = cup hilang/belum tercatat terjual. Plus = ada penjualan yang belum dicatat.</p>
        </div>
    </div>
@endunless

{{-- Penjualan per menu --}}
<div class="card">
    <div class="mb-3 text-sm font-medium">Terjual per menu</div>
    @if ($open)
        <form method="POST" action="{{ route('sesi.items', $s->id) }}" id="items-form" class="space-y-3">
            @csrf
            @foreach ($products as $p)
                @php $qty = $items[$p->id]->qty ?? 0; $price = $items[$p->id]->unit_price ?? $p->price; @endphp
                <div class="flex items-center justify-between gap-3 rounded-lg bg-neutral-950 px-3 py-2">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-medium">{{ $p->name }}</div>
                        <div class="text-xs text-neutral-500">{{ Fmt::rp($price) }}{{ $p->cups_per_unit > 1 ? ' · ' . $p->cups_per_unit . ' cup' : '' }}</div>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" class="btn btn-ghost !h-10 !w-10 !px-0 text-lg" onclick="step(this,-1)">−</button>
                        <input type="number" inputmode="numeric" min="0" name="items[{{ $p->id }}]" value="{{ $qty }}"
                               data-price="{{ $price }}" data-cups="{{ $p->cups_per_unit }}"
                               class="item-qty input !w-16 text-center" oninput="recalc()">
                        <button type="button" class="btn btn-ghost !h-10 !w-10 !px-0 text-lg" onclick="step(this,1)">+</button>
                    </div>
                </div>
            @endforeach

            <div class="grid grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="label">Polycup tambahan</label>
                    <input type="number" inputmode="numeric" min="0" name="polycup_tambah" value="{{ $s->polycup_tambah }}" class="input">
                </div>
                <div>
                    <label class="label">Polycup rusak / gratis</label>
                    <input type="number" inputmode="numeric" min="0" name="polycup_rusak" value="{{ $s->polycup_rusak }}" class="input">
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-neutral-800 pt-3">
                <div class="text-sm text-neutral-400">Total sementara: <span id="live-total" class="font-semibold text-neutral-100">{{ Fmt::rp($sum->total_sales) }}</span>
                    · <span id="live-cups">{{ Fmt::num($sum->cup_terjual) }}</span> cup</div>
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    @else
        <table class="w-full"><thead class="border-b border-neutral-800"><tr>
            <th class="th">Menu</th><th class="th text-right">Qty</th><th class="th text-right">Harga</th><th class="th text-right">Subtotal</th></tr></thead>
            <tbody class="divide-y divide-neutral-800">
            @forelse ($items as $it)
                <tr><td class="td">{{ $products->firstWhere('id', $it->product_id)->name ?? '-' }}</td>
                    <td class="td text-right">{{ $it->qty }}</td><td class="td text-right">{{ Fmt::rp($it->unit_price) }}</td>
                    <td class="td text-right font-medium">{{ Fmt::rp($it->subtotal) }}</td></tr>
            @empty
                <tr><td colspan="4" class="td text-neutral-500">Tidak ada penjualan tercatat.</td></tr>
            @endforelse
            </tbody></table>
    @endif
</div>

{{-- Pengeluaran sesi --}}
<div class="card">
    <div class="mb-3 flex items-center justify-between">
        <div class="text-sm font-medium">Pengeluaran saat sesi ini</div>
        <a href="{{ route('pengeluaran.create', ['session_id' => $s->id]) }}" class="btn btn-ghost !py-1.5">+ Catat</a>
    </div>
    @forelse ($expenses as $e)
        <div class="flex items-center justify-between border-b border-neutral-800 py-2 text-sm last:border-0">
            <span>{{ $e->description }} <span class="text-xs text-neutral-500">· {{ $e->category->name ?? '' }}</span></span>
            <span>{{ Fmt::rp($e->amount) }}</span>
        </div>
    @empty
        <p class="text-sm text-neutral-500">Belum ada.</p>
    @endforelse
</div>

{{-- Edit data sesi --}}
<details class="card">
    <summary class="cursor-pointer text-sm font-medium">Edit data sesi</summary>
    <form method="POST" action="{{ route('sesi.update', $s->id) }}" class="mt-4 space-y-3">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Tanggal</label><input type="date" name="sales_date" value="{{ $s->sales_date->format('Y-m-d') }}" class="input" required></div>
            <div><label class="label">Lokasi</label>
                <select name="location_id" class="input"><option value="">-</option>
                    @foreach ($locations as $l)<option value="{{ $l->id }}" @selected($s->location_id == $l->id)>{{ $l->name }}</option>@endforeach
                </select></div>
            <div><label class="label">Polycup awal</label><input type="number" min="0" name="polycup_awal" value="{{ $s->polycup_awal }}" class="input" required></div>
            <div><label class="label">Modal kembalian</label><input type="number" min="0" name="modal_kembalian" value="{{ (int) $s->modal_kembalian }}" class="input"></div>
        </div>
        <div><label class="label">Catatan</label><input name="notes" value="{{ $s->notes }}" class="input"></div>
        <button class="btn btn-ghost">Simpan perubahan</button>
    </form>
</details>
@endsection

@push('scripts')
<script>
    function step(btn, d) {
        const i = btn.parentElement.querySelector('input');
        i.value = Math.max(0, (parseInt(i.value) || 0) + d);
        recalc();
    }
    function recalc() {
        let total = 0, cups = 0;
        document.querySelectorAll('.item-qty').forEach(i => {
            const q = parseInt(i.value) || 0;
            total += q * parseFloat(i.dataset.price);
            cups += q * parseInt(i.dataset.cups);
        });
        const t = document.getElementById('live-total'), c = document.getElementById('live-cups');
        if (t) t.textContent = 'Rp ' + total.toLocaleString('id-ID');
        if (c) c.textContent = cups.toLocaleString('id-ID');
    }
</script>
@endpush
