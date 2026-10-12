<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        // Hari ini
        $t = DB::table('v_session_summary')->where('sales_date', $today)
            ->selectRaw('COUNT(*) sesi, COALESCE(SUM(cup_terjual),0) cup, COALESCE(SUM(total_sales),0) sales,
                         COALESCE(SUM(cash_masuk),0) cash, COALESCE(SUM(qris_masuk),0) qris')
            ->first();
        $expToday = (float) DB::table('expenses')->where('expense_date', $today)->sum('amount');

        // Bulan ini
        $m = DB::table('v_daily_report')->whereBetween('report_date', [$monthStart, $monthEnd])
            ->selectRaw('COALESCE(SUM(total_sales),0) sales, COALESCE(SUM(total_pengeluaran),0) expense, COALESCE(SUM(profit),0) profit')
            ->first();

        // 7 hari terakhir (isi hari kosong dengan 0)
        $rows = DB::table('v_daily_report')
            ->whereBetween('report_date', [now()->subDays(6)->toDateString(), $today])
            ->pluck('total_sales', 'report_date');
        $week = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $week[] = ['tgl' => $d->copy()->locale('id')->translatedFormat('D d'), 'sales' => (float) ($rows[$d->toDateString()] ?? 0)];
        }

        $openSessions = DB::table('v_session_summary')->where('status', 'open')->orderByDesc('sales_date')->get();
        $recent = DB::table('v_session_summary')->where('status', 'closed')
            ->orderByDesc('sales_date')->orderByDesc('session_id')->limit(5)->get();
        $stock = DB::table('v_polycup_stock')->first();

        return view('dashboard', compact('t', 'expToday', 'm', 'week', 'openSessions', 'recent', 'stock'));
    }
}
