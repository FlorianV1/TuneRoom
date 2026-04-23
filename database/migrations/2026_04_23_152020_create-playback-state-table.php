<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('playback_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->unique()->constrained()->cascadeOnDelete();

            $table->foreignId('current_queue_item_id')
                ->nullable()
                ->constrained('queue_items')
                ->nullOnDelete();

            $table->enum('status', ['playing', 'paused', 'stopped'])->default('stopped');

            $table->unsignedInteger('position_ms')->default(0);

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playback_states');
    }
};
