<?php

namespace AIA\API;

interface AIProviderInterface {
    public function testConnection(): array; // ['success'=>bool,'message'=>string]
    public function chat(array $conversation): array; // ['success'=>bool,'response'=>string]
}