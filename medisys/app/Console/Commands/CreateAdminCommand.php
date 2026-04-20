<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    protected $signature = 'create:admin';
    protected $description = 'Create a new admin user';

    public function handle()
    {
        $user = new User();
        $user->name = 'Admin User';
        $user->email = 'admin@example.com';
        $user->password = Hash::make('admin123');
        $user->role = 'admin';
        $user->save();

        $this->info('Admin account created successfully!');
        $this->info('Email: admin@example.com');
        $this->info('Password: admin123');
        
        return 0;
    }
}
