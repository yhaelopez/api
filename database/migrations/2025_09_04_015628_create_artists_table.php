<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('spotify_id')->unique()->nullable();
            $table->string('name');
            $table->integer('popularity')->unsigned()->nullable();
            $table->bigInteger('followers_count')->unsigned()->nullable();
            $table->userstamps();
            $table->userstampSoftDeletes();
            $table->foreignId('restored_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('restored_at')->nullable();

            // Indexes
            $table->index(['owner_id']);
            $table->index(['spotify_id']);
            $table->index(['name']);
            $table->index(['popularity']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artists');
    }
};
