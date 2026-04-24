<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_items', function (Blueprint $table) {
            // 'user' = manually added by a member
            // 'fallback' = auto-loaded from the fallback playlist
            $table->enum('source', ['user', 'fallback'])->default('user')->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('queue_items', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
