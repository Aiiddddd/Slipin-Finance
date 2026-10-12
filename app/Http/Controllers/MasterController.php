<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    private function cfg(string $type): array
    {
        $all = [
            'produk' => [
                'model' => Product::class,
                'rules' => [
                    'name' => 'required|string|max:100',
                    'price' => 'required|numeric|min:0',
                    'cogs' => 'nullable|numeric|min:0',
                    'cups_per_unit' => 'required|integer|min:1|max:10',
                ],
            ],
            'lokasi' => [
                'model' => Location::class,
                'rules' => [
                    'name' => 'required|string|max:100',
                    'notes' => 'nullable|string|max:255',
                ],
            ],
            'kategori' => [
                'model' => ExpenseCategory::class,
                'rules' => [
                    'name' => 'required|string|max:100',
                    'type' => 'required|in:bahan,operasional,peralatan,tenaga_kerja,lainnya',
                ],
            ],
        ];
        abort_unless(isset($all[$type]), 404);
        return $all[$type];
    }

    public function index()
    {
        return view('master.index', [
            'products' => Product::orderByDesc('is_active')->orderBy('name')->get(),
            'locations' => Location::orderByDesc('is_active')->orderBy('name')->get(),
            'categories' => ExpenseCategory::orderByDesc('is_active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $r, string $type)
    {
        $c = $this->cfg($type);
        $data = $r->validate($c['rules']);
        if (array_key_exists('cogs', $data)) {
            $data['cogs'] = $data['cogs'] ?? 0;
        }
        $data['is_active'] = 1;
        $c['model']::create($data);

        return back()->with('ok', 'Data ditambahkan.');
    }

    public function update(Request $r, string $type, int $id)
    {
        $c = $this->cfg($type);
        $data = $r->validate($c['rules']);
        if (array_key_exists('cogs', $data)) {
            $data['cogs'] = $data['cogs'] ?? 0;
        }
        $data['is_active'] = $r->boolean('is_active') ? 1 : 0;
        $c['model']::findOrFail($id)->update($data);

        return back()->with('ok', 'Data disimpan.');
    }
}
