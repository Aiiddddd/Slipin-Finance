<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $r)
    {
        $bulan = $r->query('bulan', now()->format('Y-m'));
        $cat = $r->query('kategori');

        $q = Expense::with('category')->whereRaw("DATE_FORMAT(expense_date, '%Y-%m') = ?", [$bulan]);
        if ($cat) {
            $q->where('category_id', $cat);
        }

        $total = (clone $q)->sum('amount');
        $expenses = $q->orderByDesc('expense_date')->orderByDesc('id')->paginate(30)->withQueryString();

        $byCat = DB::table('expenses as e')->join('expense_categories as c', 'c.id', '=', 'e.category_id')
            ->whereRaw("DATE_FORMAT(e.expense_date, '%Y-%m') = ?", [$bulan])
            ->groupBy('c.name')->orderByDesc(DB::raw('SUM(e.amount)'))
            ->selectRaw('c.name, SUM(e.amount) total')->get();

        $categories = ExpenseCategory::orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'bulan', 'cat', 'total', 'byCat', 'categories'));
    }

    public function create(Request $r)
    {
        return view('expenses.form', $this->formData(new Expense([
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_from' => 'modal_owner',
            'session_id' => $r->query('session_id'),
        ])));
    }

    public function store(Request $r)
    {
        $data = $this->validated($r);
        $data['created_by'] = $r->user()->id;
        if ($r->hasFile('receipt')) {
            $data['receipt_path'] = $r->file('receipt')->store('receipts', 'public');
        }
        Expense::create($data);

        return redirect()->route('pengeluaran.index', ['bulan' => substr($data['expense_date'], 0, 7)])
            ->with('ok', 'Pengeluaran dicatat.');
    }

    public function edit($id)
    {
        return view('expenses.form', $this->formData(Expense::findOrFail($id)));
    }

    public function update(Request $r, $id)
    {
        $e = Expense::findOrFail($id);
        $data = $this->validated($r);
        if ($r->hasFile('receipt')) {
            if ($e->receipt_path) {
                Storage::disk('public')->delete($e->receipt_path);
            }
            $data['receipt_path'] = $r->file('receipt')->store('receipts', 'public');
        }
        $e->update($data);

        return redirect()->route('pengeluaran.index', ['bulan' => substr($data['expense_date'], 0, 7)])
            ->with('ok', 'Pengeluaran diperbarui.');
    }

    public function destroy($id)
    {
        $e = Expense::findOrFail($id);
        if ($e->receipt_path) {
            Storage::disk('public')->delete($e->receipt_path);
        }
        $e->delete();

        return back()->with('ok', 'Pengeluaran dihapus.');
    }

    private function validated(Request $r): array
    {
        $data = $r->validate([
            'expense_date' => 'required|date',
            'category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'qty' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,transfer',
            'paid_from' => 'required|in:kas_jualan,modal_owner',
            'supplier' => 'nullable|string|max:100',
            'session_id' => 'nullable|exists:sales_sessions,id',
            'receipt' => 'nullable|image|max:4096',
        ]);
        unset($data['receipt']);
        return $data;
    }

    private function formData(Expense $expense): array
    {
        return [
            'expense' => $expense,
            'categories' => ExpenseCategory::where('is_active', 1)->orderBy('name')->get(),
            'sessions' => DB::table('v_session_summary')->orderByDesc('sales_date')->orderByDesc('session_id')->limit(15)->get(),
        ];
    }
}
