<?php

namespace AIA\API;

class GeminiProvider implements AIProviderInterface {
    public function __construct(private string $apiKey = '', private string $model = 'gemini-pro') {}

    public function testConnection(): array {
        if (empty($this->apiKey)) { return ['success'=>false,'message'=>'API key missing']; }
        // Placeholder test endpoint; replace with actual when available
        $res = wp_remote_get('https://generativelanguage.googleapis.com/v1beta/models?key='.$this->apiKey, [ 'timeout'=>8 ]);
        if (is_wp_error($res)) { return ['success'=>false,'message'=>$res->get_error_message()]; }
        $code = wp_remote_retrieve_response_code($res);
        return [ 'success'=> ($code>=200 && $code<300), 'message'=>'HTTP '.$code ];
    }

    public function chat(array $conversation): array {
        return ['success'=>false,'response'=>''];
    }
}