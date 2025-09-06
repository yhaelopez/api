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
        Schema::create('oauth_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable'); // Adds tokenable_id and tokenable_type for polymorphic relationship
            $table->string('provider'); // spotify, github, google, etc.
            $table->string('provider_user_id'); // The user's ID on the provider platform
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('scopes')->nullable(); // Store granted scopes
            $table->json('provider_data')->nullable(); // Store additional provider-specific data
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for performance
            $table->index(['tokenable_id', 'tokenable_type', 'provider']);
            $table->index(['provider', 'provider_user_id']);
            $table->unique(['tokenable_id', 'tokenable_type', 'provider']); // One token per provider per tokenable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_tokens');
    }
};
