<?php

namespace App\Libraries\AI;

use Config\Services;

class ClaudeClient implements AIClientInterface
{
    protected $apiKey;
    protected $model;
    protected $client;

    public function __construct()
    {
        $this->apiKey = getenv('CLAUDE_API_KEY');
        $this->model = getenv('CLAUDE_MODEL') ?: 'claude-3-5-sonnet-20241022';
        $this->client = Services::curlrequest();
    }

    public function generateText(array $messages, array $options = []): string
    {
        $url = 'https://api.anthropic.com/v1/messages';

        // Transform messages if needed. Claude API expects 'role' and 'content'.
        // System prompt is separate in newer Claude API, but can be part of messages or top level.
        // Let's extract system message if present in the array as role 'system' and move it to top level param.

        $systemPrompt = '';
        $apiMessages = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemPrompt = $msg['content'];
            } else {
                $apiMessages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ];
            }
        }

        $body = [
            'model' => $this->model,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'messages' => $apiMessages,
        ];

        if (!empty($systemPrompt)) {
            $body['system'] = $systemPrompt;
        }

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ],
                'json' => $body,
                'timeout' => 60
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['content'][0]['text'])) {
                return $result['content'][0]['text'];
            }

            log_message('error', 'Claude API Error Response: ' . $response->getBody());
            return 'Error: Could not parse Claude response.';

        } catch (\Exception $e) {
            log_message('error', 'Claude API Exception: ' . $e->getMessage());
            return 'Error: ' . $e->getMessage();
        }
    }
}
