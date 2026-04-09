<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FlexWave WYSIWYG Editor Configuration
    |--------------------------------------------------------------------------
    |
    | This file configures the FlexWave WYSIWYG editor. Publish this file
    | with: php artisan vendor:publish --tag=flexwave-wysiwyg-config
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    | The URL prefix and middleware applied to all package routes.
    | Upload routes will be at: /{route_prefix}/upload
    */
    'route_prefix' => 'flexwave',

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    | Configure how uploaded images are stored.
    |
    | disk       - Laravel filesystem disk (local, public, s3, etc.)
    | path       - Folder path within the disk
    | max_size   - Maximum file size in kilobytes (default: 5120 = 5MB)
    | allowed    - Allowed MIME types for upload
    */
    'upload' => [
        'disk'    => env('FLEXWAVE_UPLOAD_DISK', 'public'),
        'path'    => env('FLEXWAVE_UPLOAD_PATH', 'wysiwyg/uploads'),
        'max_size' => env('FLEXWAVE_MAX_SIZE', 5120),
        'allowed' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Resizing
    |--------------------------------------------------------------------------
    | Optionally resize images on upload using Intervention Image.
    |
    | enabled    - Enable/disable image resizing
    | max_width  - Maximum width in pixels (null = no limit)
    | max_height - Maximum height in pixels (null = no limit)
    | quality    - JPEG/WebP quality (1–100)
    */
    'image_resize' => [
        'enabled'    => env('FLEXWAVE_RESIZE_ENABLED', true),
        'max_width'  => env('FLEXWAVE_RESIZE_MAX_WIDTH', 1920),
        'max_height' => env('FLEXWAVE_RESIZE_MAX_HEIGHT', 1080),
        'quality'    => env('FLEXWAVE_RESIZE_QUALITY', 85),
    ],

    /*
    |--------------------------------------------------------------------------
    | Toolbar Configuration
    |--------------------------------------------------------------------------
    | Define which toolbar buttons appear in the editor and their order.
    | Remove any item to disable it globally.
    |
    | Available buttons:
    |   bold, italic, underline, strikethrough,
    |   heading, paragraph,
    |   ul, ol, blockquote, code, codeblock,
    |   link, image,
    |   alignLeft, alignCenter, alignRight, alignJustify,
    |   hr, undo, redo,
    |   preview, fullscreen, source
    */
    'toolbar' => [
        ['heading', 'paragraph'],
        ['bold', 'italic', 'underline', 'strikethrough'],
        ['ul', 'ol', 'blockquote'],
        ['code', 'codeblock'],
        ['link', 'image'],
        ['alignLeft', 'alignCenter', 'alignRight', 'alignJustify'],
        ['hr', 'undo', 'redo'],
        ['preview', 'fullscreen', 'source'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor Defaults
    |--------------------------------------------------------------------------
    | Default settings applied to every editor instance.
    | These can be overridden per-instance via Blade component attributes.
    */
    'defaults' => [
        'height'      => 400,
        'placeholder' => 'Start writing here...',
        'autofocus'   => false,
        'spellcheck'  => true,
        'dark_mode'   => 'auto',  // 'auto', 'light', 'dark'
    ],

    /*
    |--------------------------------------------------------------------------
    | Sanitization
    |--------------------------------------------------------------------------
    | HTML tags and attributes allowed in editor output.
    | Used server-side when you call Wysiwyg::sanitize($html).
    */
    'allowed_tags' => [
        'p', 'br', 'strong', 'em', 'u', 's', 'del',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'a', 'img',
        'blockquote', 'pre', 'code',
        'hr', 'div', 'span',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ],

    'allowed_attributes' => [
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        '*'   => ['class', 'id', 'style'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Plugins
    |--------------------------------------------------------------------------
    | Register custom JS plugin files to extend the editor.
    | Each entry is a path relative to the public directory.
    |
    | Example: '/js/my-wysiwyg-plugin.js'
    */
    'plugins' => [],

    /*
    |--------------------------------------------------------------------------
    | Events / Hooks
    |--------------------------------------------------------------------------
    | Laravel event classes dispatched at key moments.
    | Set to null to disable a specific event.
    */
    'events' => [
        'image_uploaded' => \FlexWave\Wysiwyg\Events\ImageUploaded::class,
        'image_deleted'  => \FlexWave\Wysiwyg\Events\ImageDeleted::class,
    ],

];
