<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Location;
use App\Models\Product;
use App\Models\SalesSession;
use App\Models\SalesSessionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function index(Request $r)
    {
        $bulan = $r->query('bulan', now()->format('Y-m'));
        $q = DB::table('v_session_summary');
        if ($bulan) {
            $q->whereRaw("DATE_FORMAT(sales_date, '%Y-%m') = ?", [$bulan]);
        }
        $sessions = $q->orderByDesc('sales_date')->orderByDesc('session_id')->paginate(25)->withQueryString();

        return view('sessions.index', compact('sessions', 'bulan'));
    }

    public function create()
    {
        $locations = Location::where('is_active', 1)->orderBy('name')->get();
        $last = SalesSession::latest('id')->first();
        return view('sessions.create', compact('locations', 'last'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'sales_date' => 'required|date',
            'location_id' => 'nullable|exists:locations,id',
            'polycup_awal' => 'required|integer|min:0',
            'modal_kembalian' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);
        $data['modal_kembalian'] = $data['modal_kembalian'] ?? 0;
        $data['user_id'] = $r->user()->id;
        $data['status'] = 'open';

        $s = SalesSession::create($data);

        return redirect()->route('sesi.show', $s->id)->with('ok', 'Sesi jualan dibuka. Selamat jualan!');
    }

    public function show($id)
    {
        $s = SalesSession::findOrFail($id);
        $sum = DB::table('v_session_summary')->where('session_id', $id)->first();
        $locations = Location::where('is_active', 1)->orderBy('name')->get();

        $items = SalesSessionItem::where('session_id', $id)->get()->keyBy('product_id');
        $products = Product::where('is_active', 1)
            ->orWhereIn('id', $items->keys())
            ->orderBy('name')->get();

        $expenses = Expense::with('category')->where('session_id', $id)->orderBy('id')->get();

        return view('sessions.show', compact('s', 'sum', 'locations', 'items', 'products', 'expenses'));
    }

    public function update(Request $r, $id)
    {
        $s = SalesSession::findOrFail($id);
        $data = $r->validate([
            'sales_date' => 'required|date',
            'location_id' => 'nullable|exists:locations,id',
            'polycup_awal' => 'required|integer|min:0',
            'modal_kembalian' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);
        $data['modal_kembalian'] = $data['modal_kembalian'] ?? 0;
        $s->update($data);

        return back()->with('ok', 'Data sesi diperbarui.');
    }

    /** Simpan qty terjual per menu + polycup tambahan/rusak */
    public function saveItems(Request $r, $id)
    {
        $s = SalesSession::findOrFail($id);
        abort_if($s->status === 'closed', 403, 'Sesi sudah ditutup.');

        $r->validate([
            'items' => 'array',
            'items.*' => 'nullable|integer|min:0|max:100000',
            'polycup_tambah' => 'nullable|integer|min:0',
            'polycup_rusak' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($r, $s) {
            foreach ((array) $r->input('items', []) as $pid => $qty) {
                $qty = (int) $qty;
                $existing = SalesSessionItem::where('session_id', $s->id)->where('product_id', $pid)->first();

                if ($qty === 0) {
                    $existing?->delete();
                    continue;
                }
                if ($existing) {
                    $existing->update(['qty' => $qty]);
                } elseif ($p = Product::find($pid)) {
                    SalesSessionItem::create([
                        'session_id' => $s->id,
                        'product_id' => $p->id,
                        'qty' => $qty,
                        'unit_price' => $p->price,
                    ]);
                }
            }
            $s->update([
                'polycup_tambah' => (int) $r->input('polycup_tambah', 0),
                'polycup_rusak' => (int) $r->input('polycup_rusak', 0),
            ]);
        });

        return back()->with('ok', 'Penjualan tersimpan.');
    }

    public function closeForm($id)
    {
        $s = SalesSession::findOrFail($id);
        if ($s->status === 'closed') {
            return redirect()->route('sesi.show', $id);
        }
        $sum = DB::table('v_session_summary')->where('session_id', $id)->first();
        return view('sessions.close', compact('s', 'sum'));
    }

    public function close(Request $r, $id)
    {
        $s = SalesSession::findOrFail($id);
        abort_if($s->status === 'closed', 403, 'Sesi sudah ditutup.');

        $data = $r->validate([
            'polycup_sisa' => 'required|integer|min:0',
            'cash_masuk' => 'required|numeric|min:0',
            'qris_masuk' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);
        $data['status'] = 'closed';
        $data['closed_at'] = now();
        $s->update($data);

        return redirect()->route('sesi.show', $id)->with('ok', 'Sesi ditutup. Cek selisih di bawah.');
    }

    public function reopen($id)
    {
        SalesSession::findOrFail($id)->update(['status' => 'open', 'closed_at' => null]);
        return redirect()->route('sesi.show', $id)->with('ok', 'Sesi dibuka kembali.');
    }

    public function destroy($id)
    {
        SalesSession::findOrFail($id)->delete();
        return redirect()->route('sesi.index')->with('ok', 'Sesi dihapus.');
    }
}
