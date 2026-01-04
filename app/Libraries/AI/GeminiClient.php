<?php

namespace App\Libraries\AI;

use Config\Services;

class GeminiClient implements AIClientInterface
{
    protected $apiKey;
    protected $model;
    protected $client;

    public function __construct()
    {
        $this->apiKey = getenv('GEMINI_API_KEY');
        $this->model = getenv('GEMINI_MODEL') ?: 'gemini-pro';
        $this->client = Services::curlrequest();
    }

    public function generateText(array $messages, array $options = []): string
    {
        // Google Gemini API (v1beta)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        // Transform messages to Gemini format (user/model roles, parts)
        $contents = [];
        $systemInstruction = null;

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                // Gemini 1.5 Pro supports system instructions, but for simplicity/compatibility we might prepend to first user message or use systemInstruction field if using 1.5
                // Let's assume using systemInstruction field logic if supported, otherwise prepend.
                // For 'gemini-pro' (1.0), system instructions are often passed as the first user prompt.
                // Let's prepend to the next user message or create a user message.
                 $contents[] = [
                    'role' => 'user',
                    'parts' => [['text' => "System Instruction: " . $msg['content']]]
                ];
                // Immediately follow with a model acknowledgment to maintain turn structure if needed, or just merge.
                // Merging is safer for simple completion.
            } else {
                $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $msg['content']]]
                ];
            }
        }

        $body = [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $options['max_tokens'] ?? 4096,
            ]
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
                'timeout' => 60
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return $result['candidates'][0]['content']['parts'][0]['text'];
            }

            log_message('error', 'Gemini API Error Response: ' . $response->getBody());
            return 'Error: Could not parse Gemini response.';

        } catch (\Exception $e) {
            log_message('error', 'Gemini API Exception: ' . $e->getMessage());
            return 'Error: ' . $e->getMessage();
        }
    }
}
