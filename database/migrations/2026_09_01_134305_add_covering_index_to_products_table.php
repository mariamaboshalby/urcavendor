<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add covering/composite indexes that directly match the products listing queries:
     *
     *  Query: WHERE is_active=1 [AND category=?] ORDER BY id DESC  (default sort)
     *  Query: WHERE is_active=1 [AND category=?] ORDER BY price ASC/DESC
     *  Query: WHERE is_active=1 [AND category=?] ORDER BY rating DESC
     *
     * These compound indexes allow MySQL to satisfy the WHERE + ORDER BY
     * using the index alone (no filesort), giving significant EXPLAIN improvement.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Composite: (is_active, category, id) — covers default sort with category filter
            if (! $this->indexExists('products', 'idx_products_active_cat_id')) {
                $table->index(['is_active', 'category', 'id'], 'idx_products_active_cat_id');
            }

            // Composite: (is_active, category, price) — covers price ASC/DESC sort
            if (! $this->indexExists('products', 'idx_products_active_cat_price')) {
                $table->index(['is_active', 'category', 'price'], 'idx_products_active_cat_price');
            }

            // Composite: (is_active, category, rating) — covers rating sort
            if (! $this->indexExists('products', 'idx_products_active_cat_rating')) {
                $table->index(['is_active', 'category', 'rating'], 'idx_products_active_cat_rating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_active_cat_id');
            $table->dropIndex('idx_products_active_cat_price');
            $table->dropIndex('idx_products_active_cat_rating');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($index) => ($index->name ?? null) === $indexName);
        }

        $indexes = \Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );
        return ! empty($indexes);
    }
};
