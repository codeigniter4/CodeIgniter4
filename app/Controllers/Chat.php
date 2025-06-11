<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ChatModel;
use App\Models\PrivateMessageModel;

class Chat extends BaseController
{
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // The check from the plan was:
        // if (!session()->get('is_logged_in')) {
        // This might cause issues if called too late, so it's often in a filter or early in constructor/initController
        // For now, let's make sure it redirects properly.
        // Using `return redirect()->to('/login');` might not work directly in initController if it expects no return.
        // Let's adjust:
        $session = session();
        if (!$session->get('is_logged_in')) {
            // Force redirect
            // Check if the current path is already /login to prevent redirect loops if something is wrong
            if (uri_string() !== 'login' && uri_string() !== '/') {
                 header('Location: ' . rtrim(site_url(), '/') . '/login');
                 exit();
            }
        }
    }

    public function index()
    {
        helper(['form']); // For CSRF field in forms if manual
        return view('chat/index');
    }

    public function getMessages()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }
        $chatModel = new ChatModel();
        $messages = $chatModel->orderBy('created_at', 'ASC')->findAll();
        return $this->response->setJSON($messages);
    }

    public function sendMessage()
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'post') {
             return $this->response->setStatusCode(403, 'Forbidden');
        }

        $validation = \Config\Services::validation();
        $validation->setRules(['message' => 'required|max_length[5000]']); // Max length example

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON(['success' => false, 'errors' => $validation->getErrors()]);
        }

        $chatModel = new ChatModel();
        $data = [
            'message' => $this->request->getPost('message')
        ];

        if ($chatModel->save($data)) {
            return $this->response->setJSON(['success' => true, 'message_id' => $chatModel->getInsertID()]);
        } else {
            // It's good practice to also log the actual database error if possible
            // log_message('error', 'ChatModel save failed: ' . print_r($chatModel->errors(), true));
            return $this->response->setJSON(['success' => false, 'errors' => 'خطا در ذخیره پیام.']); // "Error saving message."
        }
    }

    public function sendPrivateMessage()
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $validation = \Config\Services::validation();
        $validation->setRules(['private_message' => 'required|max_length[5000]']);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON(['success' => false, 'errors' => $validation->getErrors()]);
        }

        $privateMessageModel = new PrivateMessageModel();
        $data = [
            'message' => $this->request->getPost('private_message')
        ];

        if ($privateMessageModel->save($data)) {
            return $this->response->setJSON(['success' => true, 'message_id' => $privateMessageModel->getInsertID()]);
        } else {
            // log_message('error', 'PrivateMessageModel save failed: ' . print_r($privateMessageModel->errors(), true));
            return $this->response->setJSON(['success' => false, 'errors' => 'خطا در ذخیره پیام خصوصی.']); // "Error saving private message."
        }
    }

    public function getOpenAIRewrite()
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $originalMessage = $this->request->getPost('original_message');
        if (empty($originalMessage)) {
            return $this->response->setJSON(['success' => false, 'error' => 'پیام اصلی نمی تواند خالی باشد.']);
        }

        $apiKey = getenv('OPENAI_API_KEY'); // Or $_ENV['OPENAI_API_KEY']

        if (empty($apiKey) || $apiKey === 'YOUR_OPENAI_API_KEY_HERE') {
            // Simulate OpenAI response if key is not set or is placeholder
            // This allows frontend development to proceed without a real API call.
            $simulatedRewrittenText = "بازنویسی شده: " . $originalMessage . " (شبیه‌سازی شده)"; // "Rewritten: ... (simulated)"
            return $this->response->setJSON(['success' => true, 'rewritten_text' => $simulatedRewrittenText, 'simulated' => true]);
        }

        $client = \Config\Services::curlrequest([
            'baseURI' => 'https://api.openai.com/v1/',
            'timeout' => 30, // Increased timeout for API calls
        ]);

        // $prompt = "Please rewrite the following user message to enhance its anonymity and ensure it's phrased neutrally and respectfully. The language of the rewritten message should be Persian. User message: " . $originalMessage;
        // Using a more direct prompt for chat completion style
        $systemPrompt = 'You are an assistant that rewrites user messages in Persian to be anonymous, neutral, and respectful. Return only the rewritten Persian message.';

        try {
            $response = $client->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo', // Or another preferred model
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $originalMessage]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 250, // Adjust as needed, considering original message length + rewrite
                ]
            ]);

            $body = json_decode($response->getBody());

            if ($response->getStatusCode() === 200 && isset($body->choices[0]->message->content)) {
                $rewrittenText = trim($body->choices[0]->message->content);
                return $this->response->setJSON(['success' => true, 'rewritten_text' => $rewrittenText]);
            } else {
                $errorDetail = isset($body->error->message) ? $body->error->message : 'خطای ناشناخته از OpenAI.'; // "Unknown API error."
                log_message('error', 'OpenAI API Error: ' . $errorDetail . ' | Status: ' . $response->getStatusCode() . ' | Body: ' . $response->getBody());
                return $this->response->setJSON(['success' => false, 'error' => 'خطا در دریافت بازنویسی از OpenAI: ' . $errorDetail]);
            }
        } catch (\Exception $e) {
            log_message('error', 'OpenAI Request Exception: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'error' => 'خطا در هنگام درخواست از OpenAI: ' . $e->getMessage()]);
        }
    }
}
