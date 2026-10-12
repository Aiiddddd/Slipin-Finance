<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $data = $r->validate(['username' => 'required|string', 'password' => 'required|string']);

        $user = User::where('username', $data['username'])->where('is_active', 1)->first();

        if ($user && Hash::check($data['password'], $user->password_hash)) {
            Auth::login($user);
            $r->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors(['username' => 'Username atau kata sandi salah.'])->onlyInput('username');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login');
    }
}
