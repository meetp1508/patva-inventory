<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0)->after('selling_price');
            $table->integer('low_stock_threshold')->default(10)->after('stock_quantity');

            $table->index('name');
            $table->index('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['stock_quantity']);
            $table->dropColumn(['tax_rate', 'low_stock_threshold']);
        });
    }
};
