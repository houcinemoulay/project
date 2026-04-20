<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\ContactMessage;

try {
    $msg = ContactMessage::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subject' => 'Test Subject',
        'message' => 'This is a test message to verify if they are being saved.',
        'status' => 'new',
        'user_role' => 'patient'
    ]);
    echo "Message created with ID: " . $msg->id;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
