<?php

namespace AIA\Utils;

/**
 * Rate Limiter Class
 * 
 * Implements rate limiting for API calls and other operations
 */
class RateLimiter {
    
    /**
     * Check if action is allowed
     * 
     * @param string $action Action identifier
     * @param int $limit Maximum allowed attempts
     * @param int $window Time window in seconds
     * @param string $identifier User/IP identifier
     * @return bool
     */
    public static function is_allowed($action, $limit = 10, $window = 60, $identifier = null) {
        if (!$identifier) {
            $identifier = self::get_identifier();
        }
        
        $key = 'aia_rate_limit_' . md5($action . '_' . $identifier);
        $attempts = get_transient($key);
        
        if ($attempts === false) {
            set_transient($key, 1, $window);
            return true;
        }
        
        if ($attempts >= $limit) {
            return false;
        }
        
        set_transient($key, $attempts + 1, $window);
        return true;
    }
    
    /**
     * Get remaining attempts
     * 
     * @param string $action Action identifier
     * @param int $limit Maximum allowed attempts
     * @param string $identifier User/IP identifier
     * @return int
     */
    public static function get_remaining($action, $limit = 10, $identifier = null) {
        if (!$identifier) {
            $identifier = self::get_identifier();
        }
        
        $key = 'aia_rate_limit_' . md5($action . '_' . $identifier);
        $attempts = get_transient($key);
        
        if ($attempts === false) {
            return $limit;
        }
        
        return max(0, $limit - $attempts);
    }
    
    /**
     * Reset rate limit for action
     * 
     * @param string $action Action identifier
     * @param string $identifier User/IP identifier
     */
    public static function reset($action, $identifier = null) {
        if (!$identifier) {
            $identifier = self::get_identifier();
        }
        
        $key = 'aia_rate_limit_' . md5($action . '_' . $identifier);
        delete_transient($key);
    }
    
    /**
     * Get identifier for current user/request
     * 
     * @return string
     */
    private static function get_identifier() {
        $user_id = get_current_user_id();
        
        if ($user_id) {
            return 'user_' . $user_id;
        }
        
        // For non-logged in users, use IP address
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Handle proxy forwarded IPs
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        
        return 'ip_' . $ip;
    }
    
    /**
     * Apply rate limiting to AJAX action
     * 
     * @param string $action Action name
     * @param int $limit Limit
     * @param int $window Time window
     * @return bool|WP_Error
     */
    public static function check_ajax_limit($action, $limit = 30, $window = 60) {
        if (!self::is_allowed($action, $limit, $window)) {
            $remaining_time = self::get_remaining_time($action);
            wp_send_json_error(sprintf(
                __('Rate limit exceeded. Please try again in %d seconds.', 'ai-inventory-agent'),
                $remaining_time
            ), 429);
            return false;
        }
        
        return true;
    }
    
    /**
     * Get remaining time until rate limit resets
     * 
     * @param string $action Action identifier
     * @param string $identifier User/IP identifier
     * @return int Seconds remaining
     */
    private static function get_remaining_time($action, $identifier = null) {
        if (!$identifier) {
            $identifier = self::get_identifier();
        }
        
        $key = 'aia_rate_limit_' . md5($action . '_' . $identifier);
        $expiration = get_option('_transient_timeout_' . $key);
        
        if ($expiration === false) {
            return 0;
        }
        
        return max(0, $expiration - time());
    }
}