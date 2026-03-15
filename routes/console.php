<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('accounts:credentials {--export= : Export to file (e.g. storage/accounts.csv)}', function () {
    $users = User::orderBy('role')->orderBy('email')->get(['name', 'email', 'role']);
    $password = 'password'; // seeded default; real passwords are hashed and cannot be retrieved

    $rows = [];
    foreach ($users as $u) {
        $rows[] = [$u->email, $u->name, $u->role, $password];
    }

    $this->table(['Email', 'Name', 'Role', 'Password'], $rows);

    if ($file = $this->option('export')) {
        $path = base_path($file);
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fp = fopen($path, 'w');
        fputcsv($fp, ['Email', 'Name', 'Role', 'Password']);
        foreach ($users as $u) {
            fputcsv($fp, [$u->email, $u->name, $u->role, $password]);
        }
        fclose($fp);
        $this->info("Exported to {$path}");
    }
})->purpose('List all user accounts with login password (seeded accounts use "password")');
