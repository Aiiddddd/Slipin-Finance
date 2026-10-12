<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PolycupPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolycupController extends Controller
{
    public function index()
    {
        $stock = DB::table('v_polycup_stock')->first();
        $purchases = PolycupPurchase::orderByDesc('purchase_date')->orderByDesc('id')->paginate(20);

        // Riwayat pemakaian polycup 10 sesi terakhir
        $usage = DB::table('v_session_summary')->orderByDesc('sales_date')->orderByDesc('session_id')->limit(10)->get();

        return view('polycup.index', compact('stock', 'purchases', 'usage'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'purchase_date' => 'required|date',
            'qty' => 'required|integer|min:1',
            'total_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);
        $data['total_price'] = $data['total_price'] ?? 0;

        DB::transaction(function () use ($r, $data) {
            if ($r->boolean('catat_pengeluaran') && $data['total_price'] > 0) {
                $cat = ExpenseCategory::where('name', 'Polycup & Packaging')->first() ?? ExpenseCategory::first();
                $exp = Expense::create([
                    'expense_date' => $data['purchase_date'],
                    'category_id' => $cat->id,
                    'description' => 'Beli polycup ' . $data['qty'] . ' pcs',
                    'qty' => $data['qty'],
                    'unit' => 'pcs',
                    'amount' => $data['total_price'],
                    'payment_method' => 'cash',
                    'paid_from' => 'modal_owner',
                    'supplier' => $data['supplier'] ?? null,
                    'created_by' => $r->user()->id,
                ]);
                $data['expense_id'] = $exp->id;
            }
            PolycupPurchase::create($data);
        });

        return back()->with('ok', 'Pembelian polycup dicatat.');
    }

    public function destroy($id)
    {
        $p = PolycupPurchase::findOrFail($id);
        DB::transaction(function () use ($p) {
            $expenseId = $p->expense_id;
            $p->delete();
            if ($expenseId) {
                Expense::whereKey($expenseId)->delete();
            }
        });

        return back()->with('ok', 'Pembelian dihapus.');
    }
}
