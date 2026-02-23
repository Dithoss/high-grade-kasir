<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preorders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('book_id')->constrained('books')->cascadeOnDelete();
            $table->date('expected_borrow_date');
            $table->enum('status', ['waiting', 'ready', 'confirmed', 'cancelled', 'expired'])
                  ->default('waiting');
            $table->unsignedInteger('queue_position')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Satu user hanya bisa punya 1 preorder aktif per buku
            $table->unique(['user_id', 'book_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preorders');
    }
};