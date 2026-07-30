<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== CURRENT USERS IN DATABASE ===\n";
$users = User::all();
foreach ($users as $u) {
    $check1234 = Hash::check('1234', $u->password) ? 'MATCH (1234)' : (Hash::check('1322', $u->password) ? 'MATCH (1322)' : 'NO MATCH');
    echo "ID: {$u->id} | Name: {$u->name} | Username: '{$u->username}' | Email: {$u->email} | Role: {$u->role} | Password Check: {$check1234}\n";
}
