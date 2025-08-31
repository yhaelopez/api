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
        Schema::create('temporary_files', function (Blueprint $table) {
            $table->id();
            $table->string('folder');
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['folder', 'filename']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_files');
    }
};
