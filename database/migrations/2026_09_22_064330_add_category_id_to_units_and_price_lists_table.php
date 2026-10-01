<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
        });

        Schema::table('price_lists', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
        });

        $now = now();

        DB::table('categories')->insertOrIgnore([
            ['name' => 'PS 4', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PS 5', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Data unit & price list lama dimasukkan ke kategori PS 4.
        $defaultCategoryId = DB::table('categories')->where('name', 'PS 4')->value('id');

        DB::table('units')->whereNull('category_id')->update(['category_id' => $defaultCategoryId]);
        DB::table('price_lists')->whereNull('category_id')->update(['category_id' => $defaultCategoryId]);
    }

    public function down(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
