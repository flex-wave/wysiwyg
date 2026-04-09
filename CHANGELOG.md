# Changelog

All notable changes to `flexwave/wysiwyg` will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] — 2024-01-01

### Added
- Initial release
- Blade component `<x-flexwave-editor />`
- Full toolbar: bold, italic, underline, strikethrough, headings, lists, blockquote, code, codeblock, link, image, alignment, undo/redo
- Image upload via Laravel Storage with configurable disk and path
- Optional image resizing via Intervention Image v3
- Preview panel and HTML source view
- Fullscreen editing mode
- Word count and character count status bar
- Dark mode support (`auto`, `light`, `dark`)
- `Wysiwyg` facade with `sanitize()`, `toText()`, `excerpt()`, `wordCount()`
- `ImageUploaded` and `ImageDeleted` Laravel events
- Drag & drop and paste image upload
- Custom plugin system
- JavaScript public API (`FlexWave.getInstance()`, `getHTML()`, `setHTML()`, etc.)
- Custom JS events (`fw:init`, `fw:change`, `fw:uploadSuccess`, etc.)
- Full PSR-4 package structure with auto-discovery
- Publishable config, views, and assets
- PHPUnit test suite (unit + feature via Orchestra Testbench)
- Laravel Pint code style configuration
