<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function ringkas(string $content): ?string
    {
        try {
            // Gunakan API key dari env sebagai fallback jika config tidak ada
            $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
            
            if (empty($apiKey)) {
                throw new \Exception('API Key Gemini tidak dikonfigurasi. Pastikan GEMINI_API_KEY ada di file .env');
            }

            // Bersihkan konten sebelum dikirim
            $content = $this->cleanContent($content);
            
            if (empty(trim($content))) {
                throw new \Exception('Konten tidak boleh kosong');
            }

            // Potong konten jika terlalu panjang (max 30.000 karakter)
            $content = mb_substr($content, 0, 30000);

            // Log konten yang akan diproses (hanya awal)
            Log::info('Memproses ringkasan untuk konten (awal 100 karakter): ' . mb_substr($content, 0, 100));

            $prompt = "Buatkan ringkasan profesional dalam Bahasa Indonesia dari teks berikut:\n\n$content";
            $prompt .= "\n\nRingkasan harus:"
                     . "\n1. Dalam Bahasa Indonesia"
                     . "\n2. Jelas dan mudah dipahami"
                     . "\n3. Maksimal 500 kata"
                     . "\n4. Fokus pada poin-poin penting";

            // Gunakan endpoint yang benar dengan header API key
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey
            ])
            ->timeout(60) // 60 detik timeout
            ->retry(3, 1000) // 3x retry dengan delay 1 detik
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 1000,
                    'temperature' => 0.5,
                    'topP' => 0.95,
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_ONLY_HIGH'
                    ]
                ]
            ]);

            if ($response->failed()) {
                Log::error('Error Gemini API: Status: '.$response->status().' Response: '.$response->body());
                throw new \Exception('Gagal menghubungi layanan AI. Status: '.$response->status().' - '.$response->body());
            }

            $result = $response->json();
            
            // Debugging: Log full response structure
            Log::debug('Gemini API Response struktur: ' . json_encode(array_keys($result)));

            // Periksa struktur respons dengan lebih teliti
            if (!isset($result['candidates']) || empty($result['candidates'])) {
                throw new \Exception('Respons tidak memiliki kandidat: ' . json_encode($result));
            }
            
            if (!isset($result['candidates'][0]['content']) || !isset($result['candidates'][0]['content']['parts'])) {
                throw new \Exception('Struktur respons tidak valid: ' . json_encode($result['candidates'][0]));
            }
            
            if (empty($result['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Teks ringkasan kosong dalam respons');
            }

            $ringkasan = $result['candidates'][0]['content']['parts'][0]['text'];
            Log::info('Ringkasan berhasil dibuat dengan panjang: ' . strlen($ringkasan));
            
            return $ringkasan;

        } catch (\Exception $e) {
            Log::error('Error ringkas AI: '.$e->getMessage()."\nTrace: ".$e->getTraceAsString());
            return null;
        }
    }

    protected function cleanContent(string $content): string
    {
        // 1. Normalisasi line breaks
        $content = preg_replace('/\r\n|\r/', "\n", $content);
        
        // 2. Hapus karakter non-UTF8
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        
        // 3. Hapus multiple spaces
        $content = preg_replace('/\s+/', ' ', $content);
        
        // 4. Hapus karakter khusus yang mungkin mengganggu API
        $content = preg_replace('/[^\p{L}\p{N}\s\.\,\-\:\;]/u', '', $content);
        
        return trim($content);
    }
}