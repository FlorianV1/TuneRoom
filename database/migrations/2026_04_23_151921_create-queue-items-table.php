<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('queue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('added_by_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('spotify_track_id');
            $table->string('title');
            $table->string('artist');
            $table->string('album')->nullable();
            $table->string('cover_url')->nullable();
            $table->unsignedInteger('duration_ms');

            $table->unsignedSmallInteger('position')->default(0);

            $table->timestamp('played_at')->nullable();

            $table->timestamps();

            $table->index(['room_id', 'position']);
            $table->index(['room_id', 'played_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_items');
    }
};
