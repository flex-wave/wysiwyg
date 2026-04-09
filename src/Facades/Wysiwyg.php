<?php

namespace FlexWave\Wysiwyg\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string sanitize(string $html)
 * @method static string toText(string $html)
 * @method static string excerpt(string $html, int $length = 160, string $suffix = '...')
 * @method static int wordCount(string $html)
 * @method static mixed config(string $key, mixed $default = null)
 *
 * @see \FlexWave\Wysiwyg\Helpers\WysiwygHelper
 */
class Wysiwyg extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'flexwave-wysiwyg';
    }
}
