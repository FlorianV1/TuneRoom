<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 12)->unique(); // e.g. PEACH-0424
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('fallback_playlist_url')->nullable();
            $table->string('fallback_playlist_name')->nullable();

            $table->json('default_cohost_permissions')->default('{"play":true,"skip":true,"add":true}');
            $table->json('default_listener_permissions')->default('{"play":false,"skip":false,"add":true}');

            $table->enum('status', ['active', 'ended'])->default('active');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
