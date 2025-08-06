<?php

namespace AIA\Core;

use AIA\Core\MemoryManager;

/**
 * Asset Optimizer Class
 * 
 * Handles CSS/JS bundling, minification, and optimization
 */
class AssetOptimizer {
    
    /**
     * Asset cache directory
     */
    const CACHE_DIR = 'aia-cache';
    
    /**
     * Asset version for cache busting
     */
    private static $asset_version = null;
    
    /**
     * Combined CSS content
     * 
     * @var string
     */
    private static $combined_css = '';
    
    /**
     * Combined JS content
     * 
     * @var string
     */
    private static $combined_js = '';
    
    /**
     * Asset dependencies
     * 
     * @var array
     */
    private static $dependencies = [
        'css' => [],
        'js' => []
    ];
    
    /**
     * Initialize asset optimizer
     */
    public static function init() {
        // Set asset version based on plugin version and file modification times
        self::$asset_version = self::get_asset_version();
        
        // Create cache directory if needed
        self::ensure_cache_directory();
        
        // Hook into WordPress asset enqueuing
        add_action('admin_init', [__CLASS__, 'optimize_admin_assets'], 5);
        add_action('wp_enqueue_scripts', [__CLASS__, 'optimize_frontend_assets'], 5);
        
        // Clean up old cache files
        add_action('aia_daily_cleanup', [__CLASS__, 'cleanup_old_cache']);
    }
    
    /**
     * Get asset version for cache busting
     * 
     * @return string Asset version
     */
    private static function get_asset_version() {
        $version_factors = [
            AIA_PLUGIN_VERSION,
            filemtime(AIA_PLUGIN_DIR . 'assets/css/aia-combined.css'),
            filemtime(AIA_PLUGIN_DIR . 'assets/js/ui-components.js')
        ];
        
        return substr(md5(implode('|', $version_factors)), 0, 8);
    }
    
    /**
     * Ensure cache directory exists
     */
    private static function ensure_cache_directory() {
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/' . self::CACHE_DIR;
        
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
            
            // Create .htaccess for security
            $htaccess_content = "# AI Inventory Agent Cache\n";
            $htaccess_content .= "<Files ~ \"\\.(css|js)$\">\n";
            $htaccess_content .= "    Header set Cache-Control \"public, max-age=31536000\"\n";
            $htaccess_content .= "</Files>\n";
            
            file_put_contents($cache_dir . '/.htaccess', $htaccess_content);
            
            // Create index.php for security
            file_put_contents($cache_dir . '/index.php', '<?php // Silence is golden');
        }
    }
    
    /**
     * Optimize admin assets
     */
    public static function optimize_admin_assets() {
        if (!self::should_optimize()) {
            return;
        }
        
        // Check if we're on an AIA admin page
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'ai-inventory-agent') === false) {
            return;
        }
        
        // Generate or get cached optimized assets
        $optimized_css = self::get_optimized_css();
        $optimized_js = self::get_optimized_js();
        
        if ($optimized_css && $optimized_js) {
            // Dequeue individual assets
            self::dequeue_individual_assets();
            
            // Enqueue optimized assets
            wp_enqueue_style(
                'aia-optimized-css',
                $optimized_css['url'],
                [],
                self::$asset_version
            );
            
            wp_enqueue_script(
                'aia-optimized-js',
                $optimized_js['url'],
                ['jquery'],
                self::$asset_version,
                true
            );
            
            // Add inline critical CSS for above-the-fold content
            wp_add_inline_style('aia-optimized-css', self::get_critical_css());
            
            // Localize script data
            wp_localize_script('aia-optimized-js', 'aia_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('aia_ajax_nonce'),
                'strings' => [
                    'loading' => __('Loading...', 'ai-inventory-agent'),
                    'error' => __('An error occurred. Please try again.', 'ai-inventory-agent'),
                    'success' => __('Operation completed successfully.', 'ai-inventory-agent'),
                ]
            ]);
        }
    }
    
    /**
     * Optimize frontend assets
     */
    public static function optimize_frontend_assets() {
        // Frontend optimization if needed
        // Currently AIA is admin-only, but prepared for future frontend features
    }
    
    /**
     * Check if optimization should be applied
     * 
     * @return bool True if should optimize
     */
    private static function should_optimize() {
        // Don't optimize in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return false;
        }
        
        // Don't optimize if memory is low
        if (!MemoryManager::is_safe_for_operation('asset_optimization', MemoryManager::LEVEL_WARNING)) {
            return false;
        }
        
        // Check if optimization is enabled in settings
        $settings = get_option('aia_settings', []);
        return !empty($settings['enable_asset_optimization']);
    }
    
    /**
     * Get optimized CSS
     * 
     * @return array|false Optimized CSS data or false on failure
     */
    private static function get_optimized_css() {
        $cache_key = 'aia_optimized_css_' . self::$asset_version;
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        try {
            // Read and combine CSS files
            $css_content = self::combine_css_files();
            
            // Minify CSS
            $minified_css = self::minify_css($css_content);
            
            // Save to cache file
            $upload_dir = wp_upload_dir();
            $cache_dir = $upload_dir['basedir'] . '/' . self::CACHE_DIR;
            $css_file = $cache_dir . '/aia-combined-' . self::$asset_version . '.css';
            
            if (file_put_contents($css_file, $minified_css) !== false) {
                $css_url = $upload_dir['baseurl'] . '/' . self::CACHE_DIR . '/aia-combined-' . self::$asset_version . '.css';
                
                $result = [
                    'url' => $css_url,
                    'file' => $css_file,
                    'size' => strlen($minified_css)
                ];
                
                // Cache for 24 hours
                set_transient($cache_key, $result, DAY_IN_SECONDS);
                
                return $result;
            }
            
        } catch (\Exception $e) {
            error_log('AIA Asset Optimizer CSS Error: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Get optimized JavaScript
     * 
     * @return array|false Optimized JS data or false on failure
     */
    private static function get_optimized_js() {
        $cache_key = 'aia_optimized_js_' . self::$asset_version;
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        try {
            // Read and combine JS files
            $js_content = self::combine_js_files();
            
            // Minify JavaScript
            $minified_js = self::minify_js($js_content);
            
            // Save to cache file
            $upload_dir = wp_upload_dir();
            $cache_dir = $upload_dir['basedir'] . '/' . self::CACHE_DIR;
            $js_file = $cache_dir . '/aia-combined-' . self::$asset_version . '.js';
            
            if (file_put_contents($js_file, $minified_js) !== false) {
                $js_url = $upload_dir['baseurl'] . '/' . self::CACHE_DIR . '/aia-combined-' . self::$asset_version . '.js';
                
                $result = [
                    'url' => $js_url,
                    'file' => $js_file,
                    'size' => strlen($minified_js)
                ];
                
                // Cache for 24 hours
                set_transient($cache_key, $result, DAY_IN_SECONDS);
                
                return $result;
            }
            
        } catch (\Exception $e) {
            error_log('AIA Asset Optimizer JS Error: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Combine CSS files
     * 
     * @return string Combined CSS content
     */
    private static function combine_css_files() {
        // Use the new combined CSS file
        $css_file = AIA_PLUGIN_DIR . 'assets/css/aia-combined.css';
        
        if (file_exists($css_file)) {
            return file_get_contents($css_file);
        }
        
        return '';
    }
    
    /**
     * Combine JavaScript files
     * 
     * @return string Combined JS content
     */
    private static function combine_js_files() {
        $js_files = [
            AIA_PLUGIN_DIR . 'assets/js/ui-components.js',
            AIA_PLUGIN_DIR . 'assets/js/admin.js',
            AIA_PLUGIN_DIR . 'assets/js/advanced-components.js',
            AIA_PLUGIN_DIR . 'assets/js/charts.js'
        ];
        
        $combined_js = '';
        
        foreach ($js_files as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                
                // Remove debug code
                $content = self::remove_debug_code($content);
                
                // Add file separator comment
                $combined_js .= "\n/* === " . basename($file) . " === */\n";
                $combined_js .= $content . "\n";
            }
        }
        
        return $combined_js;
    }
    
    /**
     * Simple CSS minification
     * 
     * @param string $css CSS content
     * @return string Minified CSS
     */
    private static function minify_css($css) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove unnecessary whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remove whitespace around specific characters
        $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css);
        
        // Remove trailing semicolon before closing brace
        $css = preg_replace('/;(?=\s*})/', '', $css);
        
        // Remove empty rules
        $css = preg_replace('/[^{}]+\{\s*\}/', '', $css);
        
        return trim($css);
    }
    
    /**
     * Simple JavaScript minification
     * 
     * @param string $js JavaScript content
     * @return string Minified JavaScript
     */
    private static function minify_js($js) {
        // Remove single-line comments (but preserve URLs)
        $js = preg_replace('#(?<!:)//.*#', '', $js);
        
        // Remove multi-line comments
        $js = preg_replace('#/\*.*?\*/#s', '', $js);
        
        // Remove unnecessary whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        
        // Remove whitespace around operators and punctuation
        $js = preg_replace('/\s*([{}();,=+\-*\/])\s*/', '$1', $js);
        
        return trim($js);
    }
    
    /**
     * Remove debug code from JavaScript
     * 
     * @param string $js JavaScript content
     * @return string JavaScript without debug code
     */
    private static function remove_debug_code($js) {
        // Remove console.log statements
        $js = preg_replace('/console\.(log|error|warn|info|debug)\([^)]*\);?/i', '', $js);
        
        // Remove debugger statements
        $js = preg_replace('/debugger;?/i', '', $js);
        
        return $js;
    }
    
    /**
     * Get critical CSS for above-the-fold content
     * 
     * @return string Critical CSS
     */
    private static function get_critical_css() {
        return '
        .aia-admin-page{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;color:#111827;line-height:1.5}
        .aia-container{max-width:1200px;margin:0 auto;padding:0 1rem}
        .aia-card{background:#fff;border:1px solid #e5e7eb;border-radius:.5rem;box-shadow:0 1px 2px 0 rgba(0,0,0,.05)}
        .aia-button{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1rem;font-size:1rem;font-weight:500;border:1px solid transparent;border-radius:.375rem;cursor:pointer;min-height:44px}
        .aia-button--primary{background:#3b82f6;color:#fff;border-color:#3b82f6}
        ';
    }
    
    /**
     * Dequeue individual asset files
     */
    private static function dequeue_individual_assets() {
        // CSS files to dequeue
        $css_handles = [
            'aia-design-tokens',
            'aia-icons',
            'aia-layout',
            'aia-components',
            'aia-dashboard',
            'aia-chat',
            'aia-analysis',
            'aia-alerts',
            'aia-reports',
            'aia-settings',
            'aia-animations',
            'aia-responsive',
            'aia-admin-style'
        ];
        
        foreach ($css_handles as $handle) {
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
        
        // JS files to dequeue
        $js_handles = [
            'aia-ui-components',
            'aia-advanced-components',
            'aia-charts'
        ];
        
        foreach ($js_handles as $handle) {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }
    }
    
    /**
     * Clean up old cache files
     */
    public static function cleanup_old_cache() {
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/' . self::CACHE_DIR;
        
        if (!is_dir($cache_dir)) {
            return;
        }
        
        $files = glob($cache_dir . '/aia-combined-*.{css,js}', GLOB_BRACE);
        $current_time = time();
        
        foreach ($files as $file) {
            // Delete files older than 7 days
            if (filemtime($file) < $current_time - (7 * DAY_IN_SECONDS)) {
                unlink($file);
            }
        }
        
        // Clean up transients
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_aia_optimized_%' 
             OR option_name LIKE '_transient_timeout_aia_optimized_%'"
        );
    }
    
    /**
     * Get optimization statistics
     * 
     * @return array Optimization statistics
     */
    public static function get_statistics() {
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/' . self::CACHE_DIR;
        
        $stats = [
            'enabled' => self::should_optimize(),
            'version' => self::$asset_version,
            'cache_dir' => $cache_dir,
            'cache_files' => [],
            'total_cache_size' => 0,
            'original_files' => 0,
            'optimized_files' => 0
        ];
        
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '/aia-combined-*.{css,js}', GLOB_BRACE);
            
            foreach ($files as $file) {
                $size = filesize($file);
                $stats['cache_files'][] = [
                    'file' => basename($file),
                    'size' => $size,
                    'size_formatted' => size_format($size),
                    'created' => filemtime($file)
                ];
                $stats['total_cache_size'] += $size;
            }
            
            $stats['optimized_files'] = count($files);
        }
        
        // Count original files
        $original_css = glob(AIA_PLUGIN_DIR . 'assets/css/*.css');
        $original_js = glob(AIA_PLUGIN_DIR . 'assets/js/*.js');
        $stats['original_files'] = count($original_css) + count($original_js);
        
        $stats['total_cache_size_formatted'] = size_format($stats['total_cache_size']);
        
        return $stats;
    }
    
    /**
     * Clear all cached assets
     */
    public static function clear_cache() {
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/' . self::CACHE_DIR;
        
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '/aia-combined-*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        // Clear transients
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_aia_optimized_%' 
             OR option_name LIKE '_transient_timeout_aia_optimized_%'"
        );
        
        return true;
    }
}