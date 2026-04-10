<?php

namespace App\Services;

use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;

class ContentService
{
    /**
     * Generate a unique slug for a given model.
     *
     * @param string $title
     * @param string $modelClass
     * @param string|null $excludeId
     * @return string
     */
    public function generateUniqueSlug(string $title, string $modelClass, ?string $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        $query = $modelClass::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $count;
            $query = $modelClass::where('slug', $slug);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            $count++;
        }

        return $slug;
    }

    /**
     * Calculate reading time based on content.
     * Assumes 200 words per minute reading speed.
     *
     * @param string $content HTML or plain text content
     * @return int Reading time in minutes
     */
    public function calculateReadingTime(string $content): int
    {
        // Strip HTML tags
        $text = strip_tags($content);
        
        // Count words
        $wordCount = str_word_count($text);
        
        // Calculate reading time (200 words per minute)
        $minutes = ceil($wordCount / 200);
        
        // Minimum 1 minute
        return max(1, $minutes);
    }

    /**
     * Calculate reading time from content blocks.
     *
     * @param array $blocks
     * @return int
     */
    public function calculateReadingTimeFromBlocks(array $blocks): int
    {
        $totalContent = '';
        
        foreach ($blocks as $block) {
            if (isset($block['content'])) {
                if (is_array($block['content'])) {
                    // If content is an array, extract text from it
                    $totalContent .= ' ' . $this->extractTextFromArray($block['content']);
                } else {
                    $totalContent .= ' ' . $block['content'];
                }
            }
        }
        
        return $this->calculateReadingTime($totalContent);
    }

    /**
     * Extract text from nested array structure.
     *
     * @param array $data
     * @return string
     */
    private function extractTextFromArray(array $data): string
    {
        $text = '';
        
        foreach ($data as $value) {
            if (is_string($value)) {
                $text .= ' ' . $value;
            } elseif (is_array($value)) {
                $text .= ' ' . $this->extractTextFromArray($value);
            }
        }
        
        return $text;
    }

    /**
     * Sanitize HTML content to prevent XSS attacks.
     *
     * @param string $html
     * @return string
     */
    public function sanitizeHtml(string $html): string
    {
        return Purify::clean($html);
    }
}
