<?php

namespace App\Services;

use App\Libraries\AI\AIClientInterface;
use App\Libraries\AI\ClaudeClient;
use App\Libraries\AI\OpenAIClient;
use App\Libraries\AI\GeminiClient;
use App\Models\ConversationModel;
use App\Models\ProjectModel;
use App\Models\PageModel;
use App\Models\AssetModel;

class AIService
{
    protected $provider;
    protected $client;
    protected $conversationModel;
    protected $projectModel;

    public function __construct()
    {
        $this->provider = getenv('AI_DEFAULT_PROVIDER') ?: 'claude';
        $this->initClient();
        $this->conversationModel = new ConversationModel();
        $this->projectModel = new ProjectModel();
    }

    protected function initClient()
    {
        switch ($this->provider) {
            case 'openai':
                $this->client = new OpenAIClient();
                break;
            case 'gemini':
                $this->client = new GeminiClient();
                break;
            case 'claude':
            default:
                $this->client = new ClaudeClient();
                break;
        }
    }

    /**
     * Analyze project content (extracted text + images) and propose a catalog structure.
     */
    public function analyzeCatalogContent(int $projectId): array
    {
        $project = $this->projectModel->find($projectId);
        if (!$project) {
            return ['error' => 'Project not found'];
        }

        // Get Assets
        $assetModel = new AssetModel();
        $assets = $assetModel->where('project_id', $projectId)->findAll();

        $imagesList = [];
        foreach ($assets as $asset) {
            if ($asset['type'] == 'image') {
                $meta = json_decode($asset['metadata'], true) ?? [];
                $desc = $meta['description'] ?? 'No description';
                $imagesList[] = "- {$asset['original_name']} ({$desc})";
            }
        }
        $imagesText = implode("\n", $imagesList);

        $prompt = "تو یک طراح کاتالوگ حرفه‌ای هستی. کاربر می‌خواهد یک بروشور/کاتالوگ بسازد.

محتوای متنی استخراج شده:
{$project['extracted_content']}

لیست تصاویر آپلود شده:
{$imagesText}

لطفاً:
1. محتوا را تحلیل کن
2. یک ساختار پیشنهادی برای کاتالوگ ارائه بده
3. برای هر صفحه مشخص کن چه محتوایی قرار بگیرد (متن و عکس)
4. پیشنهاد بده کدام عکس‌ها کجا استفاده شوند (از لیست تصاویر بالا)

خروجی را دقیقاً به صورت JSON معتبر با این ساختار بده (بدون توضیحات اضافی قبل یا بعد از JSON):
{
  \"total_pages\": 6,
  \"pages\": [
    {
      \"page_number\": 1,
      \"layout_type\": \"cover\",
      \"title\": \"عنوان پیشنهادی\",
      \"description\": \"توضیح این صفحه\",
      \"suggested_images\": [\"image1.jpg\"],
      \"content_summary\": \"خلاصه محتوا\",
      \"content_structure\": {
          \"headline\": \"...\",
          \"subheadline\": \"...\",
          \"body_text\": \"...\"
      }
    }
  ],
  \"explanation\": \"توضیح کلی درباره ساختار پیشنهادی\"
}
";

        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        // Save System Prompt/User request to conversation history
        $this->conversationModel->insert([
            'project_id' => $projectId,
            'role' => 'user', // representing the initial analysis trigger
            'message' => 'Initial Catalog Analysis Request',
            'metadata' => json_encode(['type' => 'analysis_trigger'])
        ]);

        $response = $this->client->generateText($messages);

        // Try to parse JSON from response (sometimes AI wraps in ```json ... ```)
        $cleanJson = $this->extractJson($response);
        $structure = json_decode($cleanJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'AI JSON Parse Error: ' . json_last_error_msg() . ' Content: ' . $response);
            return [
                'success' => false,
                'error' => 'Could not parse AI response',
                'raw_response' => $response
            ];
        }

        // Save response to conversation
        $this->conversationModel->insert([
            'project_id' => $projectId,
            'role' => 'assistant',
            'message' => $structure['explanation'] ?? 'Catalog structure generated.',
            'metadata' => json_encode(['type' => 'analysis_result', 'structure' => $structure])
        ]);

        // Save pages to database
        $this->saveStructureToDatabase($projectId, $structure);

        return ['success' => true, 'data' => $structure];
    }

    /**
     * Handle chat interaction with context.
     */
    public function chat(int $projectId, string $userMessage): string
    {
        // 1. Save user message
        $this->conversationModel->insert([
            'project_id' => $projectId,
            'role' => 'user',
            'message' => $userMessage
        ]);

        // 2. Fetch history (limit to last 10-20 messages for context window)
        $history = $this->conversationModel
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'ASC') // Oldest first for context
            ->findAll(); // Limit if needed

        // 3. Build messages array
        $messages = [];
        // System prompt context
        $messages[] = [
            'role' => 'system',
            'content' => 'تو یک دستیار هوشمند طراحی کاتالوگ هستی. وظیفه تو کمک به کاربر برای ویرایش، بهبود و ساخت کاتالوگ است. پاسخ‌ها باید کوتاه، مفید و به زبان فارسی باشند.'
        ];

        foreach ($history as $h) {
            // Skip system metadata messages if not relevant text
            // But we might want to include the initial analysis result as context
            $role = ($h['role'] == 'admin') ? 'assistant' : $h['role']; // Map roles

            // If it's a structural update (JSON), we might summarize it or hide it,
            // but for now let's feed the text message.
            if (!empty($h['message'])) {
                $messages[] = ['role' => $role, 'content' => $h['message']];
            }
        }

        // 4. Call AI
        $response = $this->client->generateText($messages);

        // 5. Save AI response
        $this->conversationModel->insert([
            'project_id' => $projectId,
            'role' => 'assistant',
            'message' => $response
        ]);

        return $response;
    }

    private function saveStructureToDatabase(int $projectId, array $structure)
    {
        $pageModel = new PageModel();

        // Clear existing pages for this project (re-generation)
        $pageModel->where('project_id', $projectId)->delete();

        // Update Project total pages
        $this->projectModel->update($projectId, ['total_pages' => $structure['total_pages'] ?? 0]);

        if (isset($structure['pages']) && is_array($structure['pages'])) {
            foreach ($structure['pages'] as $page) {
                $pageModel->insert([
                    'project_id' => $projectId,
                    'page_number' => $page['page_number'],
                    'layout_type' => $page['layout_type'] ?? 'content',
                    'title' => $page['title'] ?? '',
                    'content' => json_encode($page['content_structure'] ?? []),
                    'ai_suggestions' => json_encode([
                        'description' => $page['description'] ?? '',
                        'suggested_images' => $page['suggested_images'] ?? [],
                        'content_summary' => $page['content_summary'] ?? ''
                    ]),
                    'is_approved' => false
                ]);
            }
        }
    }

    private function extractJson(string $text): string
    {
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $text, $matches)) {
            return $matches[1];
        }
        // If no markdown code blocks, assume the whole text is JSON or try to find { }
        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            return $matches[0];
        }
        return $text;
    }
}
