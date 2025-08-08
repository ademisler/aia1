<?php

namespace AIA\Core;

use AIA\Settings\Settings;

class Inventory {
    private const CACHE_KEY = 'aia_inv_summary_v1';
    private const CACHE_TTL = 300; // 5 minutes

    public function get_summary(): array {
        $cached = get_transient(self::CACHE_KEY);
        if ($cached !== false) { return $cached; }

        if (!class_exists('WC_Product')) {
            $summary = [ 'counts'=>['total_products'=>0,'low_stock'=>0,'out_of_stock'=>0], 'updated_at'=>current_time('mysql') ];
            set_transient(self::CACHE_KEY, $summary, self::CACHE_TTL);
            return $summary;
        }

        $threshold = Settings::get()['low_stock_threshold'] ?? 5;

        // Total published products
        $total = (int) wp_count_posts('product')->publish;

        // Out of stock count via meta query
        $oos = new \WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                [ 'key' => '_stock_status', 'value' => 'outofstock' ]
            ]
        ]);
        $out_of_stock = $oos->found_posts;

        // Low stock (in stock but _stock <= threshold)
        $low = new \WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [ 'key' => '_stock_status', 'value' => 'instock' ],
                [ 'key' => '_manage_stock', 'value' => 'yes' ],
                [ 'key' => '_stock', 'value' => (string) $threshold, 'compare' => '<=', 'type' => 'NUMERIC' ],
            ]
        ]);
        $low_stock = $low->found_posts;

        $summary = [
            'counts' => [
                'total_products' => $total,
                'low_stock' => $low_stock,
                'out_of_stock' => $out_of_stock,
            ],
            'updated_at' => current_time('mysql'),
        ];
        set_transient(self::CACHE_KEY, $summary, self::CACHE_TTL);
        return $summary;
    }
}