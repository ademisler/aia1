<?php

namespace AIA\API;

class OpenAIProvider implements AIProviderInterface {
    private string $apiKey;
    private string $model;

    public function __construct(string $apiKey = '', string $model = 'gpt-3.5-turbo') {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function testConnection(): array {
        if (empty($this->apiKey)) { return ['success'=>false,'message'=>'API key missing']; }
        $res = wp_remote_get('https://api.openai.com/v1/models', [
            'headers' => [ 'Authorization' => 'Bearer '.$this->apiKey ],
            'timeout' => 8,
        ]);
        if (is_wp_error($res)) { return ['success'=>false,'message'=>$res->get_error_message()]; }
        $code = wp_remote_retrieve_response_code($res);
        if ($code >= 200 && $code < 300) { return ['success'=>true,'message'=>'OpenAI reachable']; }
        return ['success'=>false,'message'=>'HTTP '.$code];
    }

    public function chat(array $conversation): array {
        if (empty($this->apiKey)) { return ['success'=>false,'response'=>'']; }
        $messages = [];
        foreach ($conversation as $m) {
            $messages[] = [ 'role'=> $m['role'] ?? 'user', 'content'=> $m['content'] ?? '' ];
        }
        $body = [ 'model'=> $this->model, 'messages'=> $messages, 'temperature'=> 0.2 ];
        $res = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [ 'Authorization' => 'Bearer '.$this->apiKey, 'Content-Type'=>'application/json' ],
            'body' => wp_json_encode($body),
            'timeout' => 15,
        ]);
        if (is_wp_error($res)) { return ['success'=>false,'response'=>'']; }
        $code = wp_remote_retrieve_response_code($res);
        $data = json_decode(wp_remote_retrieve_body($res), true);
        if ($code >= 200 && $code < 300 && isset($data['choices'][0]['message']['content'])) {
            return ['success'=>true,'response'=>$data['choices'][0]['message']['content']];
        }
        return ['success'=>false,'response'=>''];
    }
}