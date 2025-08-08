<?php

namespace AIA\API;

class DummyProvider implements AIProviderInterface {
    public function __construct(private string $apiKey = '') {}

    public function testConnection(): array {
        return ['success'=>true,'message'=>'Dummy provider active'];
    }

    public function chat(array $conversation): array {
        $last = end($conversation);
        $msg = is_array($last) && isset($last['content']) ? $last['content'] : '';
        return [
            'success'=>true,
            'response'=>__('This is a placeholder response. Configure a real AI provider to get intelligent answers.','ai-inventory-agent') . ' (echo: ' . esc_html($msg) . ')'
        ];
    }
}