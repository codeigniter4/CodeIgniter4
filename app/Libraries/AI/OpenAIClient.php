<?php

namespace App\Libraries\AI;

use Config\Services;

class OpenAIClient implements AIClientInterface
{
    protected $apiKey;
    protected $model;
    protected $client;

    public function __construct()
    {
        $this->apiKey = getenv('OPENAI_API_KEY');
        $this->model = getenv('OPENAI_MODEL') ?: 'gpt-4-turbo-preview';
        $this->client = Services::curlrequest();
    }

    public function generateText(array $messages, array $options = []): string
    {
        $url = 'https://api.openai.com/v1/chat/completions';

        $body = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? 4096,
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
                'timeout' => 60
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['choices'][0]['message']['content'])) {
                return $result['choices'][0]['message']['content'];
            }

            log_message('error', 'OpenAI API Error Response: ' . $response->getBody());
            return 'Error: Could not parse OpenAI response.';

        } catch (\Exception $e) {
            log_message('error', 'OpenAI API Exception: ' . $e->getMessage());
            return 'Error: ' . $e->getMessage();
        }
    }
}
