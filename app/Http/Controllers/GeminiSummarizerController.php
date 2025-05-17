<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeminiSummarizerController extends Controller
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function summarize(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        try {
            $response = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent', [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "Please provide a concise summary of the following text:\n\n" . $request->text
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return response()->json([
                    'success' => true,
                    'summary' => $result['candidates'][0]['content']['parts'][0]['text']
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate summary: ' . $response->status()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
