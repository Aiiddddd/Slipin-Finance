@extends('layouts.app')
@section('title', 'Buka Sesi Jualan')
@section('content')
<div class="mx-auto max-w-lg space-y-4">
    <h1 class="text-xl font-semibold">Buka Sesi Jualan</h1>
    <form method="POST" action="{{ route('sesi.store') }}" class="card space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="label">Tanggal</label>
                <input type="date" name="sales_date" value="{{ old('sales_date', now()->toDateString()) }}" class="input" required>
            </div>
            <div>
                <label class="label">Lokasi</label>
                <select name="location_id" class="input">
                    <option value="">- pilih -</option>
                    @foreach ($locations as $l)
                        <option value="{{ $l->id }}" @selected(old('location_id', $last?->location_id) == $l->id)>{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="label">Stok awal polycup (yang dibawa)</label>
            <input type="number" inputmode="numeric" min="0" name="polycup_awal" value="{{ old('polycup_awal') }}" class="input" placeholder="mis. 100" required autofocus>
        </div>
        <div>
            <label class="label">Modal kembalian (receh)</label>
            <input type="number" inputmode="numeric" min="0" step="1000" name="modal_kembalian" value="{{ old('modal_kembalian', 0) }}" class="input">
            <p class="hint">Uang receh yang dibawa. Tidak dihitung sebagai hasil penjualan.</p>
        </div>
        <div>
            <label class="label">Catatan (opsional)</label>
            <input name="notes" value="{{ old('notes') }}" class="input" placeholder="mis. event car free day">
        </div>
        <button class="btn btn-primary w-full">Buka Sesi</button>
    </form>
</div>
@endsection
