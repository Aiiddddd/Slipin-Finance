<!doctype html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Masuk - Slipin Coffee</title>
    @include('layouts.head')
</head>
<body class="flex min-h-screen items-center justify-center bg-neutral-800 px-4 py-8 text-neutral-100 antialiased">
<div class="w-full max-w-sm">
    <div class="mb-6 flex flex-col items-center text-center">
        <img src="/icons/logo-full.png" class="mb-5 h-52 w-auto" alt="Slipin Coffee">
        <h1 class="text-xl font-semibold">Masuk ke akun Anda</h1>
        <p class="mt-1 text-sm text-neutral-300">Masukkan kredensial Anda di bawah untuk masuk</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4 rounded-2xl border border-neutral-700 bg-neutral-950/50 p-5">
        @csrf
        <div>
            <label class="label" for="username">Username</label>
            <input id="username" name="username" value="{{ old('username') }}" class="input" placeholder="Username Anda"
                   autocomplete="username" autofocus required>
            @error('username')<p class="mt-1 text-xs text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="password">Kata Sandi</label>
            <input id="password" type="password" name="password" class="input" placeholder="Kata Sandi"
                   autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary w-full">Masuk</button>
    </form>
</div>
</body>
</html>
