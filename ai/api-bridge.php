<?php
/**
 * AI API Bridge — plug in OpenAI / Gemini here.
 * When enabled, all AI functions use the real API.
 * When disabled (default), the engine uses keyword matching.
 */

define('AI_API_ENABLED', false);          // Set true when you have a key
define('AI_API_TYPE', 'openai');          // 'openai' or 'gemini'
define('AI_API_KEY', '');                 // Your API key
define('AI_MODEL', 'gpt-3.5-turbo');      // or 'gemini-pro'

// ─── Chat ───────────────────────────────────────────────────────────────────
function aiChatResponse(string $message): string {
    if (!AI_API_ENABLED || !AI_API_KEY) return '';
    // OpenAI example:
    // $response = callOpenAI([...]); return $response;
    return '';
}

// ─── Product Recommendations ────────────────────────────────────────────────
function aiProductRecommend(string $keywords): array {
    if (!AI_API_ENABLED || !AI_API_KEY) return [];
    // Return array of product IDs
    return [];
}

// ─── Greeting Generation ────────────────────────────────────────────────────
function aiGenerateGreeting(string $category, string $tone, string $name): string {
    if (!AI_API_ENABLED || !AI_API_KEY) return '';
    return '';
}

// ─── Image Analysis ─────────────────────────────────────────────────────────
function aiAnalyzeImage(string $imagePath): array {
    if (!AI_API_ENABLED || !AI_API_KEY) return [];
    return [];
}

// ─── Smart Search ───────────────────────────────────────────────────────────
function aiSmartSearch(string $query): array {
    if (!AI_API_ENABLED || !AI_API_KEY) return [];
    return [];
}
