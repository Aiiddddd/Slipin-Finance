@extends('layouts.app')
@section('title', 'Tutup Sesi')
@section('content')
@use('App\Support\Fmt')
<div class="mx-auto max-w-lg space-y-4">
    <a href="{{ route('sesi.show', $s->id) }}" class="text-xs text-neutral-400 underline">← kembali ke sesi</a>
    <h1 class="text-xl font-semibold">Tutup Sesi · {{ Fmt::tgl($s->sales_date, 'd M Y') }}</h1>

    <div class="grid grid-cols-2 gap-3">
        <div class="card"><div class="stat-l">Total Sales (catatan)</div><div class="stat">{{ Fmt::rp($sum->total_sales) }}</div></div>
        <div class="card"><div class="stat-l">Sisa Polycup (seharusnya)</div><div class="stat">{{ Fmt::num($sum->sisa_seharusnya) }}</div></div>
    </div>

    <form method="POST" action="{{ route('sesi.close', $s->id) }}" class="card space-y-4">
        @csrf
        <div>
            <label class="label">Sisa polycup (hitung fisik)</label>
            <input type="number" inputmode="numeric" min="0" id="sisa" name="polycup_sisa" value="{{ old('polycup_sisa') }}" class="input" required autofocus>
            <p class="hint" id="sisa-info">Hitung polycup yang masih tersisa di tas/box.</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="label">Uang cash masuk</label>
                <input type="number" inputmode="numeric" min="0" id="cash" name="cash_masuk" value="{{ old('cash_masuk') }}" class="input" required>
            </div>
            <div>
                <label class="label">Uang QRIS masuk</label>
                <input type="number" inputmode="numeric" min="0" id="qris" name="qris_masuk" value="{{ old('qris_masuk') }}" class="input" required>
            </div>
        </div>
        <p class="hint -mt-2">Cash = hasil jualan saja, <b>tanpa</b> modal kembalian ({{ Fmt::rp($s->modal_kembalian) }}).</p>
        <div id="uang-info" class="rounded-lg bg-neutral-950 px-3 py-2 text-sm text-neutral-400">Total uang masuk: Rp 0</div>
        <div>
            <label class="label">Catatan penutupan (opsional)</label>
            <input name="notes" value="{{ old('notes', $s->notes) }}" class="input" placeholder="mis. 1 cup gratis buat tetangga">
        </div>
        <button class="btn btn-primary w-full">Tutup Sesi</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const expectedSisa = {{ (int) $sum->sisa_seharusnya }};
    const totalSales = {{ (int) $sum->total_sales }};
    const rp = n => (n < 0 ? '-' : '') + 'Rp ' + Math.abs(n).toLocaleString('id-ID');
    function upd() {
        const sisa = document.getElementById('sisa').value;
        const si = document.getElementById('sisa-info');
        if (sisa !== '') {
            const d = parseInt(sisa) - expectedSisa;
            si.textContent = d === 0 ? 'Pas dengan catatan ✓' : 'Selisih ' + (d > 0 ? '+' : '') + d + ' cup dari catatan';
            si.className = 'mt-1 text-xs ' + (d === 0 ? 'text-emerald-400' : 'text-red-400');
        }
        const cash = parseInt(document.getElementById('cash').value) || 0;
        const qris = parseInt(document.getElementById('qris').value) || 0;
        const info = document.getElementById('uang-info');
        const d = cash + qris - totalSales;
        info.textContent = 'Total uang masuk: ' + rp(cash + qris) + ' · ' + (d === 0 ? 'pas dengan sales ✓' : 'selisih ' + (d > 0 ? '+' : '') + rp(d));
        info.className = 'rounded-lg bg-neutral-950 px-3 py-2 text-sm ' + ((cash + qris) === 0 ? 'text-neutral-400' : (d === 0 ? 'text-emerald-400' : 'text-red-400'));
    }
    ['sisa', 'cash', 'qris'].forEach(id => document.getElementById(id).addEventListener('input', upd));
    upd();
</script>
@endpush
