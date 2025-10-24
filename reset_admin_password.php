<?php
// Script untuk reset password admin dan cek data
// Jalankan dengan: php reset_admin_password.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "\n========================================\n";
echo "RESET PASSWORD ADMIN\n";
echo "========================================\n\n";

// Cek admin
$admin = User::where('email', 'admin@fishmonitoring.com')->first();

if (!$admin) {
    echo "❌ Admin tidak ditemukan!\n";
    echo "Membuat admin baru...\n\n";
    
    $admin = User::create([
        'name' => 'Admin IoT Fish',
        'email' => 'admin@fishmonitoring.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'is_active' => true,
    ]);
    
    echo "✅ Admin berhasil dibuat!\n";
} else {
    echo "✅ Admin ditemukan!\n";
    echo "   ID: {$admin->id}\n";
    echo "   Nama: {$admin->name}\n";
    echo "   Email: {$admin->email}\n";
    echo "   Role: {$admin->role}\n";
    echo "   Status: " . ($admin->is_active ? 'Active' : 'Inactive') . "\n\n";
    
    // Reset password
    echo "🔄 Mereset password ke 'password123'...\n";
    $admin->password = Hash::make('password123');
    $admin->is_active = true; // Pastikan aktif
    $admin->save();
    echo "✅ Password berhasil direset!\n\n";
}

// Test password
echo "🧪 Testing password...\n";
if (Hash::check('password123', $admin->password)) {
    echo "✅ Password 'password123' BENAR!\n\n";
} else {
    echo "❌ Password tidak cocok!\n\n";
}

echo "========================================\n";
echo "KREDENSIAL LOGIN ADMIN:\n";
echo "========================================\n";
echo "Email: admin@fishmonitoring.com\n";
echo "Password: password123\n";
echo "URL: http://127.0.0.1:8000/login\n";
echo "========================================\n\n";

// Cek semua users
echo "DAFTAR SEMUA USER:\n";
echo "========================================\n";
$users = User::all();
foreach ($users as $user) {
    echo "- {$user->name} ({$user->email}) - Role: {$user->role}\n";
}
echo "\nTotal users: " . $users->count() . "\n";
echo "========================================\n\n";
