<?php

namespace AIA\API;

class GeminiProvider implements AIProviderInterface {
    private string $apiKey;
    private string $model;

    public function __construct(string $apiKey = '', string $model = 'gemini-2.0-flash') {
        $this->apiKey = $apiKey;
        $this->model = $model ?: 'gemini-2.0-flash';
    }

    public function testConnection(): array {
        if (empty($this->apiKey)) { return ['success'=>false,'message'=>'API key missing']; }
        $endpoint = sprintf('https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent', $this->model);
        $body = [
            'contents' => [ [ 'parts' => [ [ 'text' => 'ping' ] ] ] ]
        ];
        $res = wp_remote_post($endpoint, [
            'headers' => [ 'Content-Type' => 'application/json', 'X-goog-api-key' => $this->apiKey ],
            'body' => wp_json_encode($body),
            'timeout' => 10,
        ]);
        if (is_wp_error($res)) { return ['success'=>false,'message'=>$res->get_error_message()]; }
        $code = wp_remote_retrieve_response_code($res);
        return [ 'success'=> ($code>=200 && $code<300), 'message'=>'HTTP '.$code ];
    }

    public function chat(array $conversation): array {
        if (empty($this->apiKey)) { return ['success'=>false,'response'=>'']; }
        // Use the last user message; if not found, use last entry content
        $text = '';
        for ($i=count($conversation)-1; $i>=0; $i--) {
            $m = $conversation[$i];
            if (($m['role'] ?? 'user') === 'user' && !empty($m['content'])) { $text = (string)$m['content']; break; }
        }
        if ($text === '' && !empty($conversation)) { $text = (string)($conversation[count($conversation)-1]['content'] ?? ''); }
        $endpoint = sprintf('https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent', $this->model);
        $payload = [ 'contents' => [ [ 'parts' => [ [ 'text' => $text ] ] ] ] ];
        $res = wp_remote_post($endpoint, [
            'headers' => [ 'Content-Type' => 'application/json', 'X-goog-api-key' => $this->apiKey ],
            'body' => wp_json_encode($payload),
            'timeout' => 20,
        ]);
        if (is_wp_error($res)) { return ['success'=>false,'response'=>'']; }
        $code = wp_remote_retrieve_response_code($res);
        $data = json_decode(wp_remote_retrieve_body($res), true);
        if ($code>=200 && $code<300 && !empty($data['candidates'][0]['content']['parts'][0]['text'])) {
            return ['success'=>true,'response'=>$data['candidates'][0]['content']['parts'][0]['text']];
        }
        return ['success'=>false,'response'=>''];
    }
}