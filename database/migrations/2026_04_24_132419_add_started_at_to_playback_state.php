<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('playback_states', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('position_ms');
        });
    }

    public function down(): void
    {
        Schema::table('playback_states', function (Blueprint $table) {
            $table->dropColumn('started_at');
        });
    }
};
