<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function range(string $bulan): array
    {
        $start = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $bulan . '-01')->startOfMonth();
        return [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()];
    }

    public function index(Request $r)
    {
        $bulan = $r->query('bulan', now()->format('Y-m'));
        [$from, $to] = $this->range($bulan);

        $daily = DB::table('v_daily_report')->whereBetween('report_date', [$from, $to])->orderBy('report_date')->get();

        $totals = (object) [
            'sales' => $daily->sum('total_sales'),
            'cash' => $daily->sum('cash_masuk'),
            'qris' => $daily->sum('qris_masuk'),
            'expense' => $daily->sum('total_pengeluaran'),
            'profit' => $daily->sum('profit'),
        ];

        $products = DB::table('sales_session_items as i')
            ->join('sales_sessions as s', 's.id', '=', 'i.session_id')
            ->join('products as p', 'p.id', '=', 'i.product_id')
            ->whereBetween('s.sales_date', [$from, $to])
            ->groupBy('p.id', 'p.name')->orderByDesc(DB::raw('SUM(i.qty)'))
            ->selectRaw('p.name, SUM(i.qty) qty, SUM(i.subtotal) omzet')->get();

        $expenseByCat = DB::table('expenses as e')->join('expense_categories as c', 'c.id', '=', 'e.category_id')
            ->whereBetween('e.expense_date', [$from, $to])
            ->groupBy('c.name')->orderByDesc(DB::raw('SUM(e.amount)'))
            ->selectRaw('c.name, SUM(e.amount) total')->get();

        $byLocation = DB::table('v_session_summary')->whereBetween('sales_date', [$from, $to])
            ->groupBy('location')->orderByDesc(DB::raw('SUM(total_sales)'))
            ->selectRaw("COALESCE(location,'(tanpa lokasi)') location, COUNT(*) sesi, SUM(cup_terjual) cup,
                         SUM(total_sales) sales, ROUND(AVG(total_sales)) rata")->get();

        $check = DB::table('v_session_summary')->whereBetween('sales_date', [$from, $to])->where('status', 'closed')
            ->selectRaw('COALESCE(SUM(selisih_polycup),0) polycup, COALESCE(SUM(selisih_uang),0) uang, COUNT(*) sesi')->first();

        return view('reports.index', compact('bulan', 'daily', 'totals', 'products', 'expenseByCat', 'byLocation', 'check'));
    }

    public function export(Request $r)
    {
        $bulan = $r->query('bulan', now()->format('Y-m'));
        [$from, $to] = $this->range($bulan);

        $rows = DB::table('v_session_summary')->whereBetween('sales_date', [$from, $to])
            ->orderBy('sales_date')->orderBy('session_id')->get();
        $exp = DB::table('expenses as e')->join('expense_categories as c', 'c.id', '=', 'e.category_id')
            ->whereBetween('e.expense_date', [$from, $to])->orderBy('e.expense_date')
            ->select('e.expense_date', 'c.name as kategori', 'e.description', 'e.qty', 'e.unit', 'e.amount', 'e.payment_method', 'e.paid_from', 'e.supplier')->get();

        return response()->streamDownload(function () use ($rows, $exp) {
            $o = fopen('php://output', 'w');
            fwrite($o, "\xEF\xBB\xBF"); // BOM supaya Excel baca UTF-8
            fputcsv($o, ['LAPORAN PENJUALAN']);
            fputcsv($o, ['Tanggal', 'Lokasi', 'Crew', 'Polycup Awal', 'Tambah', 'Rusak', 'Terjual', 'Sisa Seharusnya', 'Sisa Fisik', 'Selisih Polycup', 'Total Sales', 'Cash', 'QRIS', 'Selisih Uang', 'Pengeluaran Sesi']);
            foreach ($rows as $x) {
                fputcsv($o, [$x->sales_date, $x->location, $x->crew, $x->polycup_awal, $x->polycup_tambah, $x->polycup_rusak, $x->cup_terjual, $x->sisa_seharusnya, $x->sisa_fisik, $x->selisih_polycup, $x->total_sales, $x->cash_masuk, $x->qris_masuk, $x->selisih_uang, $x->pengeluaran_sesi]);
            }
            fputcsv($o, []);
            fputcsv($o, ['PENGELUARAN']);
            fputcsv($o, ['Tanggal', 'Kategori', 'Keterangan', 'Qty', 'Satuan', 'Jumlah', 'Metode', 'Sumber Dana', 'Supplier']);
            foreach ($exp as $x) {
                fputcsv($o, [$x->expense_date, $x->kategori, $x->description, $x->qty, $x->unit, $x->amount, $x->payment_method, $x->paid_from, $x->supplier]);
            }
            fclose($o);
        }, "slipin-coffee-{$bulan}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
