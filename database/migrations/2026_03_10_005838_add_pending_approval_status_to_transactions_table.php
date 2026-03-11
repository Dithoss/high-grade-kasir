<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN status ENUM(
                'pending_approval',
                'borrowed',
                'return_requested',
                'returned',
                'late',
                'lost',
                'damaged',
                'rejected'
            ) DEFAULT 'borrowed'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN status ENUM(
                'borrowed',
                'return_requested',
                'returned',
                'late',
                'lost',
                'damaged'
            ) DEFAULT 'borrowed'
        ");
    }
};