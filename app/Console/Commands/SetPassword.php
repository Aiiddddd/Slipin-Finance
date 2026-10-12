<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetPassword extends Command
{
    protected $signature = 'slipin:set-password {username} {--role= : owner atau crew (hanya untuk user baru)}';
    protected $description = 'Set / ganti password user. Kalau username belum ada, user baru dibuat.';

    public function handle(): int
    {
        $pass = $this->secret('Password baru (min. 6 karakter)');
        $conf = $this->secret('Ulangi password');

        if ($pass !== $conf || strlen((string) $pass) < 6) {
            $this->error('Password tidak sama atau kurang dari 6 karakter.');
            return self::FAILURE;
        }

        $user = User::firstOrNew(['username' => $this->argument('username')]);
        if (! $user->exists) {
            $user->name = ucfirst($this->argument('username'));
            $user->role = $this->option('role') === 'owner' ? 'owner' : 'crew';
            $user->is_active = 1;
        }
        $user->password_hash = Hash::make($pass);
        $user->save();

        $this->info("Password untuk '{$user->username}' ({$user->role}) berhasil disimpan.");
        return self::SUCCESS;
    }
}
