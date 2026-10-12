@extends('layouts.app')
@section('title', $expense->exists ? 'Edit Pengeluaran' : 'Catat Pengeluaran')
@section('content')
@use('App\Support\Fmt')
<div class="mx-auto max-w-lg space-y-4">
    <a href="{{ route('pengeluaran.index') }}" class="text-xs text-neutral-400 underline">← semua pengeluaran</a>
    <h1 class="text-xl font-semibold">{{ $expense->exists ? 'Edit' : 'Catat' }} Pengeluaran</h1>

    <form method="POST" enctype="multipart/form-data" class="card space-y-4"
          action="{{ $expense->exists ? route('pengeluaran.update', $expense->id) : route('pengeluaran.store') }}">
        @csrf
        @if ($expense->exists) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="label">Tanggal</label>
                <input type="date" name="expense_date" class="input" required
                       value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d')) }}">
            </div>
            <div>
                <label class="label">Kategori</label>
                <select name="category_id" class="input" required>
                    <option value="">- pilih -</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $expense->category_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="label">Keterangan</label>
            <input name="description" class="input" required placeholder="mis. Es batu 2 plastik, Susu UHT"
                   value="{{ old('description', $expense->description) }}">
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="label">Qty</label>
                <input type="number" step="0.01" min="0" inputmode="decimal" name="qty" class="input" value="{{ old('qty', $expense->qty ? (float) $expense->qty : '') }}">
            </div>
            <div>
                <label class="label">Satuan</label>
                <input name="unit" class="input" list="units" placeholder="kg / liter" value="{{ old('unit', $expense->unit) }}">
                <datalist id="units"><option>kg</option><option>liter</option><option>pcs</option><option>pack</option><option>plastik</option><option>galon</option><option>dus</option></datalist>
            </div>
            <div>
                <label class="label">Total (Rp)</label>
                <input type="number" min="0" inputmode="numeric" name="amount" class="input" required value="{{ old('amount', $expense->amount !== null ? (int) $expense->amount : '') }}">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="label">Dibayar pakai</label>
                <select name="payment_method" class="input">
                    @foreach (['cash' => 'Cash', 'qris' => 'QRIS', 'transfer' => 'Transfer'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('payment_method', $expense->payment_method) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Sumber dana</label>
                <select name="paid_from" class="input">
                    <option value="modal_owner" @selected(old('paid_from', $expense->paid_from) === 'modal_owner')>Modal owner</option>
                    <option value="kas_jualan" @selected(old('paid_from', $expense->paid_from) === 'kas_jualan')>Kas jualan</option>
                </select>
            </div>
        </div>

        <div>
            <label class="label">Supplier / toko (opsional)</label>
            <input name="supplier" class="input" value="{{ old('supplier', $expense->supplier) }}">
        </div>

        <div>
            <label class="label">Terkait sesi jualan (opsional)</label>
            <select name="session_id" class="input">
                <option value="">- tidak terkait -</option>
                @foreach ($sessions as $s)
                    <option value="{{ $s->session_id }}" @selected(old('session_id', $expense->session_id) == $s->session_id)>
                        {{ Fmt::tgl($s->sales_date, 'd M Y') }} · {{ $s->location ?? 'tanpa lokasi' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label">Foto nota (opsional)</label>
            <input type="file" name="receipt" accept="image/*" capture="environment"
                   class="block w-full text-sm text-neutral-400 file:mr-3 file:rounded-lg file:border-0 file:bg-neutral-800 file:px-3 file:py-2 file:text-neutral-200">
            @if ($expense->receipt_path)<p class="hint">Sudah ada nota. Upload baru untuk mengganti.</p>@endif
        </div>

        <button class="btn btn-primary w-full">Simpan</button>
    </form>
</div>
@endsection
