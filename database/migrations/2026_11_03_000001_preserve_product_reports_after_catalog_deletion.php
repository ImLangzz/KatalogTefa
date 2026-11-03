<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reports', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('product_reports', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
        });

        Schema::table('product_reports', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('product_reports')->whereNull('product_id')->delete();

        Schema::table('product_reports', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('product_reports', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable(false)->change();
        });

        Schema::table('product_reports', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }
};
