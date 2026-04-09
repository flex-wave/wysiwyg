<?php

namespace FlexWave\Wysiwyg\Tests;

use FlexWave\Wysiwyg\Helpers\WysiwygHelper;
use PHPUnit\Framework\TestCase;

class WysiwygHelperTest extends TestCase
{
    protected WysiwygHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new WysiwygHelper([
            'allowed_tags' => [
                'p', 'br', 'strong', 'em', 'u',
                'h1', 'h2', 'h3', 'a', 'img',
                'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
            ],
            'allowed_attributes' => [
                'a'   => ['href', 'title', 'target', 'rel'],
                'img' => ['src', 'alt', 'width', 'height'],
                '*'   => ['class'],
            ],
        ]);
    }

    public function test_sanitize_strips_disallowed_tags(): void
    {
        $input  = '<p>Hello</p><script>alert("xss")</script>';
        $output = $this->helper->sanitize($input);

        $this->assertStringContainsString('<p>Hello</p>', $output);
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringNotContainsString('alert', $output);
    }

    public function test_sanitize_strips_disallowed_attributes(): void
    {
        $input  = '<p onclick="evil()" class="text">Hello</p>';
        $output = $this->helper->sanitize($input);

        $this->assertStringContainsString('class="text"', $output);
        $this->assertStringNotContainsString('onclick', $output);
    }

    public function test_sanitize_blocks_javascript_href(): void
    {
        $input  = '<a href="javascript:void(0)">Click</a>';
        $output = $this->helper->sanitize($input);

        $this->assertStringNotContainsString('javascript:', $output);
    }

    public function test_to_text_strips_html(): void
    {
        $html   = '<p>Hello <strong>world</strong></p>';
        $output = $this->helper->toText($html);

        $this->assertSame('Hello world', trim($output));
    }

    public function test_to_text_converts_br_to_newline(): void
    {
        $html   = 'Line one<br>Line two';
        $output = $this->helper->toText($html);

        $this->assertStringContainsString("\n", $output);
    }

    public function test_excerpt_truncates_correctly(): void
    {
        $html    = '<p>' . str_repeat('word ', 100) . '</p>';
        $excerpt = $this->helper->excerpt($html, 50, '...');

        $this->assertLessThanOrEqual(53, mb_strlen($excerpt)); // 50 chars + '...'
        $this->assertStringEndsWith('...', $excerpt);
    }

    public function test_excerpt_returns_full_text_when_short(): void
    {
        $html    = '<p>Short text</p>';
        $excerpt = $this->helper->excerpt($html, 200);

        $this->assertSame('Short text', $excerpt);
    }

    public function test_word_count(): void
    {
        $html  = '<p>The quick brown fox jumps over the lazy dog</p>';
        $count = $this->helper->wordCount($html);

        $this->assertSame(9, $count);
    }

    public function test_word_count_empty(): void
    {
        $this->assertSame(0, $this->helper->wordCount(''));
    }

    public function test_config_returns_value(): void
    {
        $helper = new WysiwygHelper(['foo' => ['bar' => 'baz']]);
        $this->assertSame('baz', $helper->config('foo.bar'));
        $this->assertSame('default', $helper->config('foo.missing', 'default'));
    }
}
