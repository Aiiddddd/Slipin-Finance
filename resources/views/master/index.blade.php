@extends('layouts.app')
@section('title', 'Master Data')
@section('content')
@use('App\Support\Fmt')
<h1 class="text-xl font-semibold">Master Data</h1>

{{-- ================= MENU / PRODUK ================= --}}
<div class="card space-y-3">
    <div class="text-sm font-medium">Menu / Produk</div>
    <p class="hint">Harga di sini dipakai untuk penjualan <b>berikutnya</b>. Penjualan lama tetap memakai harga saat dicatat. Menu tidak dihapus, cukup dinonaktifkan.</p>

    @foreach ($products as $p)
        <form method="POST" action="{{ route('master.update', ['produk', $p->id]) }}" class="grid grid-cols-2 items-end gap-2 border-b border-neutral-800 pb-3 lg:grid-cols-12">
            @csrf @method('PUT')
            <div class="col-span-2 lg:col-span-4"><label class="label">Nama</label><input name="name" value="{{ $p->name }}" class="input" required></div>
            <div class="lg:col-span-2"><label class="label">Harga</label><input type="number" min="0" name="price" value="{{ (int) $p->price }}" class="input" required></div>
            <div class="lg:col-span-2"><label class="label">HPP (opsional)</label><input type="number" min="0" name="cogs" value="{{ (int) $p->cogs }}" class="input"></div>
            <div class="lg:col-span-1"><label class="label">Cup</label><input type="number" min="1" max="10" name="cups_per_unit" value="{{ $p->cups_per_unit }}" class="input" required></div>
            <label class="flex items-center gap-2 pb-2 text-sm lg:col-span-1"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($p->is_active) class="h-4 w-4"> Aktif</label>
            <div class="lg:col-span-2"><button class="btn btn-ghost w-full">Simpan</button></div>
        </form>
    @endforeach

    <form method="POST" action="{{ route('master.store', 'produk') }}" class="grid grid-cols-2 items-end gap-2 lg:grid-cols-12">
        @csrf
        <div class="col-span-2 lg:col-span-4"><label class="label">Menu baru</label><input name="name" class="input" placeholder="mis. Matcha Latte" required></div>
        <div class="lg:col-span-2"><label class="label">Harga</label><input type="number" min="0" name="price" class="input" required></div>
        <div class="lg:col-span-2"><label class="label">HPP</label><input type="number" min="0" name="cogs" class="input" value="0"></div>
        <div class="lg:col-span-1"><label class="label">Cup</label><input type="number" min="1" max="10" name="cups_per_unit" value="1" class="input" required></div>
        <div class="col-span-2 lg:col-span-3"><button class="btn btn-primary w-full">+ Tambah menu</button></div>
    </form>
</div>

{{-- ================= LOKASI ================= --}}
<div class="card space-y-3">
    <div class="text-sm font-medium">Lokasi / Titik Mangkal</div>
    @foreach ($locations as $l)
        <form method="POST" action="{{ route('master.update', ['lokasi', $l->id]) }}" class="grid grid-cols-2 items-end gap-2 border-b border-neutral-800 pb-3 lg:grid-cols-12">
            @csrf @method('PUT')
            <div class="lg:col-span-4"><label class="label">Nama</label><input name="name" value="{{ $l->name }}" class="input" required></div>
            <div class="col-span-2 lg:col-span-5"><label class="label">Catatan</label><input name="notes" value="{{ $l->notes }}" class="input"></div>
            <label class="flex items-center gap-2 pb-2 text-sm lg:col-span-1"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($l->is_active) class="h-4 w-4"> Aktif</label>
            <div class="lg:col-span-2"><button class="btn btn-ghost w-full">Simpan</button></div>
        </form>
    @endforeach
    <form method="POST" action="{{ route('master.store', 'lokasi') }}" class="grid grid-cols-2 items-end gap-2 lg:grid-cols-12">
        @csrf
        <div class="lg:col-span-4"><label class="label">Lokasi baru</label><input name="name" class="input" placeholder="mis. Depan Kampus" required></div>
        <div class="col-span-2 lg:col-span-5"><label class="label">Catatan</label><input name="notes" class="input"></div>
        <div class="col-span-2 lg:col-span-3"><button class="btn btn-primary w-full">+ Tambah lokasi</button></div>
    </form>
</div>

{{-- ================= KATEGORI PENGELUARAN ================= --}}
@php $catTypes = ['bahan' => 'Bahan', 'operasional' => 'Operasional', 'peralatan' => 'Peralatan', 'tenaga_kerja' => 'Tenaga kerja', 'lainnya' => 'Lainnya']; @endphp
<div class="card space-y-3">
    <div class="text-sm font-medium">Kategori Pengeluaran</div>
    @foreach ($categories as $c)
        <form method="POST" action="{{ route('master.update', ['kategori', $c->id]) }}" class="grid grid-cols-2 items-end gap-2 border-b border-neutral-800 pb-3 lg:grid-cols-12">
            @csrf @method('PUT')
            <div class="lg:col-span-5"><label class="label">Nama</label><input name="name" value="{{ $c->name }}" class="input" required></div>
            <div class="lg:col-span-3"><label class="label">Jenis</label>
                <select name="type" class="input">@foreach ($catTypes as $v => $lbl)<option value="{{ $v }}" @selected($c->type === $v)>{{ $lbl }}</option>@endforeach</select></div>
            <label class="flex items-center gap-2 pb-2 text-sm lg:col-span-1"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($c->is_active) class="h-4 w-4"> Aktif</label>
            <div class="col-span-2 lg:col-span-3"><button class="btn btn-ghost w-full">Simpan</button></div>
        </form>
    @endforeach
    <form method="POST" action="{{ route('master.store', 'kategori') }}" class="grid grid-cols-2 items-end gap-2 lg:grid-cols-12">
        @csrf
        <div class="lg:col-span-5"><label class="label">Kategori baru</label><input name="name" class="input" placeholder="mis. Topping / Cup Sealer" required></div>
        <div class="lg:col-span-3"><label class="label">Jenis</label>
            <select name="type" class="input">@foreach ($catTypes as $v => $lbl)<option value="{{ $v }}">{{ $lbl }}</option>@endforeach</select></div>
        <div class="col-span-2 lg:col-span-4"><button class="btn btn-primary w-full">+ Tambah kategori</button></div>
    </form>
</div>
@endsection
