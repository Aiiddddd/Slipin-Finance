<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index', ['users' => User::orderBy('role')->orderBy('name')->get()]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|alpha_dash|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:owner,crew',
        ]);
        $data['password_hash'] = Hash::make($data['password']);
        unset($data['password']);
        $data['is_active'] = 1;
        User::create($data);

        return back()->with('ok', 'User ditambahkan.');
    }

    public function update(Request $r, $id)
    {
        $u = User::findOrFail($id);
        $data = $r->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|in:owner,crew',
            'password' => 'nullable|string|min:6',
        ]);

        $isSelf = $u->id === $r->user()->id;
        $data['is_active'] = $isSelf ? 1 : ($r->boolean('is_active') ? 1 : 0);
        if ($isSelf) {
            $data['role'] = 'owner'; // jangan sampai owner kehilangan akses sendiri
        }
        if (! empty($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
        }
        unset($data['password']);
        $u->update($data);

        return back()->with('ok', 'User disimpan.');
    }
}
