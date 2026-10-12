@extends('layouts.app')
@section('title', 'Sesi Jualan')
@section('content')
@use('App\Support\Fmt')
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-xl font-semibold">Sesi Jualan</h1>
    <div class="flex items-center gap-2">
        <form method="GET" class="flex items-center gap-2">
            <input type="month" name="bulan" value="{{ $bulan }}" class="input !w-auto" onchange="this.form.submit()">
        </form>
        <a href="{{ route('sesi.create') }}" class="btn btn-primary">+ Buka Sesi</a>
    </div>
</div>

<div class="card !p-0 overflow-x-auto">
    <table class="w-full">
        <thead class="border-b border-neutral-800"><tr>
            <th class="th">Tanggal</th><th class="th">Lokasi</th><th class="th">Crew</th>
            <th class="th text-right">Cup</th><th class="th text-right">Sales</th>
            <th class="th text-right">Cash</th><th class="th text-right">QRIS</th>
            <th class="th text-right">Selisih Uang</th><th class="th text-right">Selisih Cup</th><th class="th">Status</th>
        </tr></thead>
        <tbody class="divide-y divide-neutral-800">
        @forelse ($sessions as $x)
            <tr class="cursor-pointer hover:bg-neutral-800/50" onclick="location='{{ route('sesi.show', $x->session_id) }}'">
                <td class="td"><a href="{{ route('sesi.show', $x->session_id) }}" class="underline">{{ Fmt::tgl($x->sales_date, 'D, d M') }}</a></td>
                <td class="td">{{ $x->location ?? '-' }}</td>
                <td class="td text-neutral-400">{{ $x->crew ?? '-' }}</td>
                <td class="td text-right">{{ Fmt::num($x->cup_terjual) }}</td>
                <td class="td text-right font-medium">{{ Fmt::rp($x->total_sales) }}</td>
                <td class="td text-right">{{ $x->status === 'closed' ? Fmt::rp($x->cash_masuk) : '-' }}</td>
                <td class="td text-right">{{ $x->status === 'closed' ? Fmt::rp($x->qris_masuk) : '-' }}</td>
                <td class="td text-right {{ $x->status === 'closed' ? Fmt::selClass($x->selisih_uang) : 'text-neutral-600' }}">{{ $x->status === 'closed' ? Fmt::selRp($x->selisih_uang) : '-' }}</td>
                <td class="td text-right {{ $x->status === 'closed' ? Fmt::selClass($x->selisih_polycup) : 'text-neutral-600' }}">{{ $x->status === 'closed' ? Fmt::sel($x->selisih_polycup) : '-' }}</td>
                <td class="td"><span class="badge {{ $x->status === 'open' ? 'bg-lime-900 text-lime-300' : 'bg-neutral-800 text-neutral-300' }}">{{ $x->status === 'open' ? 'Berjalan' : 'Selesai' }}</span></td>
            </tr>
        @empty
            <tr><td colspan="10" class="td py-8 text-center text-neutral-500">Belum ada sesi di bulan ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $sessions->links() }}
@endsection
