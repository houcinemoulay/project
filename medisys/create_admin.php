<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create admin user
$user = new User();
$user->name = 'Admin User';
$user->email = 'admin@example.com';
$user->password = Hash::make('admin123');
$user->role = 'admin';
$user->save();

echo "Admin account created successfully!\n";
echo "Email: admin@example.com\n";
echo "Password: admin123\n";
