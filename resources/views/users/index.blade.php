@extends('layouts.app')
@section('title', 'User')
@section('content')
<h1 class="text-xl font-semibold">User</h1>
<p class="hint">Owner: akses penuh. Crew: buka/tutup sesi, input penjualan, dan catat pengeluaran (tanpa laporan profit, master data, dan hapus data).</p>

<div class="card space-y-3">
    @foreach ($users as $u)
        <form method="POST" action="{{ route('user.update', $u->id) }}" class="grid grid-cols-2 items-end gap-2 border-b border-neutral-800 pb-3 lg:grid-cols-12">
            @csrf @method('PUT')
            <div class="lg:col-span-3"><label class="label">Nama <span class="text-neutral-500">({{ $u->username }})</span></label><input name="name" value="{{ $u->name }}" class="input" required></div>
            <div class="lg:col-span-2"><label class="label">Role</label>
                <select name="role" class="input">
                    <option value="owner" @selected($u->role === 'owner')>Owner</option>
                    <option value="crew" @selected($u->role === 'crew')>Crew</option>
                </select></div>
            <div class="col-span-2 lg:col-span-3"><label class="label">Password baru</label><input type="password" name="password" class="input" placeholder="kosongkan jika tidak diganti" autocomplete="new-password"></div>
            <label class="flex items-center gap-2 pb-2 text-sm lg:col-span-2">
                <input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($u->is_active) @disabled($u->id === auth()->id()) class="h-4 w-4"> Aktif</label>
            <div class="lg:col-span-2"><button class="btn btn-ghost w-full">Simpan</button></div>
        </form>
    @endforeach
</div>

<form method="POST" action="{{ route('user.store') }}" class="card grid grid-cols-2 items-end gap-2 lg:grid-cols-12">
    @csrf
    <div class="col-span-2 text-sm font-medium lg:col-span-12">Tambah user</div>
    <div class="lg:col-span-3"><label class="label">Nama</label><input name="name" class="input" required></div>
    <div class="lg:col-span-3"><label class="label">Username</label><input name="username" class="input" required autocomplete="off"></div>
    <div class="lg:col-span-2"><label class="label">Password</label><input type="password" name="password" class="input" required autocomplete="new-password"></div>
    <div class="lg:col-span-2"><label class="label">Role</label><select name="role" class="input"><option value="crew">Crew</option><option value="owner">Owner</option></select></div>
    <div class="col-span-2 lg:col-span-2"><button class="btn btn-primary w-full">+ Tambah</button></div>
</form>
@endsection
