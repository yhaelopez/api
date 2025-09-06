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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('spotify_id')->nullable();
            $table->string('google_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('restored_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('restored_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
