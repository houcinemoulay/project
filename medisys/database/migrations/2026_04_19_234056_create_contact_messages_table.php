<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOTE: This migration is intentionally a no-op.
 * The contact_messages table is created in migration
 * 2026_04_19_201029_create_contact_messages_table.php
 * This file exists only to preserve migration history order.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Table already created in earlier migration - safe no-op
    }

    public function down(): void
    {
        // No-op
    }
};
