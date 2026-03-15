<?php
/**
 * One-off script: list all accounts with password.
 * Seeded users all use password: password (stored hashed in DB).
 *
 * Run: php export_credentials.php
 * Or use Artisan: php artisan accounts:credentials
 * Export to CSV: php artisan accounts:credentials --export=storage/accounts.csv
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\Models\User::orderBy('role')->orderBy('email')->get(['name', 'email', 'role']);
$password = 'password';

// CSV header
$out = [['Email', 'Name', 'Role', 'Password']];
foreach ($users as $u) {
    $out[] = [$u->email, $u->name, $u->role, $password];
}

$path = __DIR__ . '/storage/accounts_with_passwords.csv';
$dir = dirname($path);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$fp = fopen($path, 'w');
foreach ($out as $row) {
    fputcsv($fp, $row);
}
fclose($fp);

echo "Exported " . count($users) . " accounts to:\n" . $path . "\n";
echo "\nAll seeded accounts use password: password\n";
