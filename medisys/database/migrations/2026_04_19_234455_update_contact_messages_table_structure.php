<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOTE: This migration is intentionally a no-op.
 * The status column is already included in the canonical
 * 2026_04_19_201029_create_contact_messages_table.php migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Already handled in the base migration - safe no-op
    }

    public function down(): void
    {
        // No-op
    }
};
