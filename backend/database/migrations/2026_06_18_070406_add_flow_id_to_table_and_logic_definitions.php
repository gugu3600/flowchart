<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_definitions', function (Blueprint $table) {
            $table->foreignId('flow_id')->nullable()->constrained('flows')->cascadeOnDelete()->after('user_id');
        });

        Schema::table('logic_definitions', function (Blueprint $table) {
            $table->foreignId('flow_id')->nullable()->constrained('flows')->cascadeOnDelete()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('table_definitions', function (Blueprint $table) {
            $table->dropForeign(['flow_id']);
            $table->dropColumn('flow_id');
        });

        Schema::table('logic_definitions', function (Blueprint $table) {
            $table->dropForeign(['flow_id']);
            $table->dropColumn('flow_id');
        });
    }
};
