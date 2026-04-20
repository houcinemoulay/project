<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\ContactMessage;

try {
    $messages = ContactMessage::all();
    echo "Count: " . $messages->count() . "\n";
    foreach ($messages as $msg) {
        echo "ID: {$msg->id}, Name: {$msg->name}, Subject: {$msg->subject}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
