<?php

namespace App\Services;

use App\Libraries\DocxParser;

class FileParserService
{
    protected $docxParser;

    public function __construct()
    {
        $this->docxParser = new DocxParser();
    }

    public function parseDocx(string $filePath): string
    {
        try {
            return $this->docxParser->parse($filePath);
        } catch (\Exception $e) {
            log_message('error', 'Docx Parsing Error: ' . $e->getMessage());
            return '';
        }
    }

    public function analyzeImage(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $info = getimagesize($filePath);
        if (!$info) {
            return [];
        }

        $width = $info[0];
        $height = $info[1];
        $mime = $info['mime'];

        // Aspect ratio
        $ratio = $height > 0 ? $width / $height : 0;

        return [
            'width' => $width,
            'height' => $height,
            'aspect_ratio' => $ratio,
            'mime' => $mime,
            'description' => null // Placeholder for AI description
        ];
    }
}
