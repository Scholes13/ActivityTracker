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
        Schema::table('activities', function (Blueprint $table) {
            $table->string('external_id')->nullable()->after('completed_at')->comment('Unique ID for public submissions');
            $table->string('external_name')->nullable()->after('external_id')->comment('Name of external submitter');
            $table->string('external_email')->nullable()->after('external_name')->comment('Email of external submitter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'external_name', 'external_email']);
        });
    }
}; 