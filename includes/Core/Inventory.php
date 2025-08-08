<?php

namespace AIA\Core;

use AIA\Settings\Settings;

class Inventory {
    private const CACHE_KEY = 'aia_inv_summary_v1';
    private const CACHE_TTL = 300; // 5 minutes

    public static function clear_cache(): void {
        delete_transient(self::CACHE_KEY);
    }

    public function get_summary(): array {
        $cached = get_transient(self::CACHE_KEY);
        if ($cached !== false) { return $cached; }

        if (!class_exists('WC_Product')) {
            $summary = [ 'counts'=>['total_products'=>0,'low_stock'=>0,'out_of_stock'=>0], 'updated_at'=>current_time('mysql') ];
            set_transient(self::CACHE_KEY, $summary, self::CACHE_TTL);
            return $summary;
        }

        $threshold = Settings::get()['low_stock_threshold'] ?? 5;

        $total = (int) wp_count_posts('product')->publish;

        $oos = new \WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [ [ 'key' => '_stock_status', 'value' => 'outofstock' ] ]
        ]);
        $out_of_stock = $oos->found_posts;

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

        $summary = [ 'counts' => [ 'total_products' => $total, 'low_stock' => $low_stock, 'out_of_stock' => $out_of_stock ], 'updated_at' => current_time('mysql') ];
        set_transient(self::CACHE_KEY, $summary, self::CACHE_TTL);
        return $summary;
    }

    public function get_low_stock(int $limit = 10, int $page = 1, ?string $category = null): array {
        if (!class_exists('WC_Product')) { return ['items'=>[], 'page'=>1, 'has_more'=>false]; }
        $threshold = Settings::get()['low_stock_threshold'] ?? 5;
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => max(1,$limit),
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [ 'key' => '_stock_status', 'value' => 'instock' ],
                [ 'key' => '_manage_stock', 'value' => 'yes' ],
                [ 'key' => '_stock', 'value' => (string) $threshold, 'compare' => '<=', 'type' => 'NUMERIC' ],
            ],
            'orderby' => 'meta_value_num',
            'meta_key' => '_stock',
            'order' => 'ASC',
            'paged' => max(1,$page),
        ];
        if ($category) {
            $args['tax_query'] = [ [ 'taxonomy'=>'product_cat', 'field'=>'slug', 'terms'=> sanitize_title($category) ] ];
        }
        $q = new \WP_Query($args);
        $items = [];
        foreach ($q->posts as $pid) {
            $product = function_exists('wc_get_product') ? wc_get_product($pid) : null;
            $items[] = [
                'id' => $pid,
                'name' => get_the_title($pid),
                'sku' => $product ? $product->get_sku() : get_post_meta($pid, '_sku', true),
                'price' => $product ? (float)$product->get_price() : (float)get_post_meta($pid, '_price', true),
                'stock' => (int) get_post_meta($pid, '_stock', true),
                'edit_url' => get_edit_post_link($pid, ''),
                'permalink' => get_permalink($pid),
            ];
        }
        $has_more = ($q->max_num_pages > $args['paged']);
        return [ 'items'=>$items, 'page'=>$args['paged'], 'has_more'=>$has_more ];
    }
}