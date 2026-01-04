<?php

namespace App\Libraries\AI;

interface AIClientInterface
{
    /**
     * Generate text response from the AI provider.
     *
     * @param array $messages Array of messages [['role' => 'user', 'content' => '...'], ...]
     * @param array $options Additional options (max_tokens, temperature, etc.)
     * @return string The generated text
     */
    public function generateText(array $messages, array $options = []): string;
}
