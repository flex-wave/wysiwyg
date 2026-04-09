<?php

namespace FlexWave\Wysiwyg\Helpers;

class WysiwygHelper
{
    public function __construct(protected array $config) {}

    /**
     * Sanitize HTML output from the editor.
     * Strips disallowed tags and attributes.
     */
    public function sanitize(string $html): string
    {
        $allowedTags = $this->config['allowed_tags'] ?? [];
        $allowedAttrs = $this->config['allowed_attributes'] ?? [];

        if (empty($allowedTags)) {
            return strip_tags($html);
        }

        // Build tag string for strip_tags
        $tagString = '<' . implode('><', $allowedTags) . '>';
        $html = strip_tags($html, $tagString);

        // Sanitize attributes using DOMDocument
        if (! extension_loaded('dom')) {
            return $html;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        foreach ($xpath->query('//*') as $node) {
            /** @var \DOMElement $node */
            $tag = strtolower($node->nodeName);
            $allowed = array_merge($allowedAttrs['*'] ?? [], $allowedAttrs[$tag] ?? []);

            $attrsToRemove = [];
            foreach ($node->attributes as $attr) {
                if (! in_array($attr->name, $allowed)) {
                    $attrsToRemove[] = $attr->name;
                }

                // Block javascript: in href/src
                if (in_array($attr->name, ['href', 'src'])) {
                    if (str_starts_with(trim(strtolower($attr->value)), 'javascript:')) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
            }

            foreach ($attrsToRemove as $attr) {
                $node->removeAttribute($attr);
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body) {
            return $html;
        }

        $result = '';
        foreach ($body->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }

    /**
     * Convert editor HTML to plain text.
     */
    public function toText(string $html): string
    {
        return strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));
    }

    /**
     * Generate a safe excerpt from editor HTML.
     */
    public function excerpt(string $html, int $length = 160, string $suffix = '...'): string
    {
        $text = $this->toText($html);
        $text = preg_replace('/\s+/', ' ', trim($text));

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $length)) . $suffix;
    }

    /**
     * Get word count from editor HTML.
     */
    public function wordCount(string $html): int
    {
        $text = $this->toText($html);
        return str_word_count(strip_tags($text));
    }

    /**
     * Get the config value.
     */
    public function config(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
