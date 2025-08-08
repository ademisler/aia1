<?php

namespace AIA\Settings;

class Settings {
    private const OPTION = 'aia_settings_v3';

    public static function defaults(): array {
        return [
            'ai_provider' => 'dummy',
            'api_key' => '',
            'low_stock_threshold' => 5,
            'model' => '' ,
        ];
    }

    public static function get(): array {
        $saved = get_option(self::OPTION, []);
        return wp_parse_args(is_array($saved)? $saved: [], self::defaults());
    }

    public static function update(array $data): bool {
        $current = self::get();
        $merged = array_merge($current, self::sanitize($data));
        return update_option(self::OPTION, $merged);
    }

    public static function sanitize(array $data): array {
        $out = [];
        if (isset($data['ai_provider'])) {
            $allowed = ['dummy','openai','gemini'];
            $val = sanitize_text_field($data['ai_provider']);
            $out['ai_provider'] = in_array($val, $allowed, true) ? $val : 'dummy';
        }
        if (isset($data['api_key'])) {
            $out['api_key'] = sanitize_text_field($data['api_key']);
        }
        if (isset($data['low_stock_threshold'])) {
            $out['low_stock_threshold'] = max(0, intval($data['low_stock_threshold']));
        }
        if (isset($data['model'])) {
            $out['model'] = sanitize_text_field($data['model']);
        }
        return $out;
    }
}