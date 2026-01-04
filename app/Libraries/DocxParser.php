<?php

namespace App\Libraries;

/**
 * DocxParser Library
 * Extracts text from DOCX files without external dependencies like php-zip (uses built-in ZipArchive).
 */
class DocxParser
{
    /**
     * Parse a DOCX file and return its text content.
     *
     * @param string $filePath Absolute path to the .docx file
     * @return string Extracted text
     * @throws \Exception
     */
    public function parse(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: " . $filePath);
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            // Check for word/document.xml
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $xml = $zip->getFromIndex($index);
                $zip->close();
                return $this->extractTextFromXml($xml);
            }
            $zip->close();
            throw new \Exception("Invalid DOCX format: word/document.xml not found.");
        } else {
            throw new \Exception("Could not open DOCX file.");
        }
    }

    /**
     * Extract text from XML content.
     */
    private function extractTextFromXml(string $xmlContent): string
    {
        $dom = new \DOMDocument();
        // Suppress XML errors
        libxml_use_internal_errors(true);
        $dom->loadXML($xmlContent);
        libxml_clear_errors();

        $output = '';

        // Find all paragraphs (<w:p>)
        $paragraphs = $dom->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'p');

        if ($paragraphs->length === 0) {
             // Fallback: try finding elements by tag name without NS if previous failed (though DOMDocument handles NS usually)
             $paragraphs = $dom->getElementsByTagName('w:p');
        }

        foreach ($paragraphs as $p) {
            $text = '';
            // Within paragraph, find runs (<w:r>) and then text (<w:t>)
            // Or just get textContent of paragraph, but sometimes we want spacing.
            // Let's use simple textContent for now, but handle newlines.

            $text = $p->textContent;
            if (!empty(trim($text))) {
                $output .= trim($text) . "\n\n";
            }
        }

        // Fallback if structured parsing failed significantly
        if (empty($output)) {
            $output = strip_tags($xmlContent);
        }

        return trim($output);
    }
}
