<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiApiService
{
    protected $apiKey;
    protected $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-latest:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function extractJobData(string $rawText): ?array
    {
        $prompt = 'Extract the following job posting into a valid JSON object with EXACTLY these keys: "job_title" (string), "company_name" (string, use null if not found), "company_logo" (string URL, search the text for any image URLs that represent the company logo, usually ending in .jpg, .png, or starting with domain-specific CDN paths, use null if not found), "requirements" (array of strings, extract key skills and qualifications as bullet points). DO NOT return any markdown formatting, only pure JSON string that can be parsed by json_decode. Raw text: ' . $rawText;

        // Menggunakan endpoint OpenAI-compatible dari Google AI Studio
        $url = env('AI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions');
        $apiKey = env('GEMINI_API_KEY'); // Menggunakan API Key Gemini (Google AI Studio)
        $model = env('AI_MODEL', 'gemini-3.1-flash-lite'); // Menggunakan model Gemini 3.1 Flash Lite

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->post($url, [
            'model' => $model,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => 'You are a JSON job data extractor. You must only output a valid JSON object.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;
            
            \Illuminate\Support\Facades\Log::info('Gemma AI Response:', ['content' => $content]);
            
            if ($content) {
                // Ensure no markdown formatting (like ```json ... ```) just in case
                $content = preg_replace('/```json\s*/', '', $content);
                $content = preg_replace('/```\s*/', '', $content);
                return json_decode($content, true);
            }
        } else {
            \Illuminate\Support\Facades\Log::error('Gemma AI Error:', ['status' => $response->status(), 'body' => $response->body()]);
            
            // Fallback untuk MVP jika API gagal (misal API key tidak valid)
            $lines = explode("\n", trim($rawText));
            $title = $lines[0] ?? 'Unknown Job Title';
            if (strlen($title) > 100) $title = substr($title, 0, 100) . '...';
            
            return [
                'job_title' => $title . ' (AI Failed)',
                'company_name' => 'API Error',
                'requirements' => [
                    'Peringatan: Gemma AI gagal mengekstrak data karena konfigurasi API key tidak valid atau terjadi limit.',
                    'HTTP Status: ' . $response->status(),
                    'Teks Mentah: ' . substr($rawText, 0, 200) . '...'
                ]
            ];
        }

        return null;
    }
}
