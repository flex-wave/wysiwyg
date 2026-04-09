@props([
    'name'        => 'content',
    'value'       => '',
    'placeholder' => '',
    'height'      => 400,
    'required'    => false,
    'readonly'    => false,
    'id'          => '',
    'class'       => '',
])

@php
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
    $hasError = $errors->has($errorKey);
@endphp

<div
    class="fw-wysiwyg-wrapper {{ $class }}"
    data-fw-editor="{{ $editorId }}"
    data-fw-options="{{ htmlspecialchars($optionsJson, ENT_QUOTES, 'UTF-8') }}"
>
    {{-- Hidden textarea that stores the actual HTML value --}}
    <textarea
        id="{{ $editorId }}-input"
        name="{{ $name }}"
        style="display:none"
        @if($required) required @endif
        @if($readonly) readonly @endif
        aria-label="{{ $placeholder }}"
    >{{ old($errorKey, $value) }}</textarea>

    {{-- Editor Shell --}}
    <div
        id="{{ $editorId }}"
        class="fw-editor {{ $hasError ? 'fw-editor--error' : '' }}"
        role="application"
        aria-label="Rich text editor"
        data-dark-mode="{{ $darkMode }}"
    >
        {{-- Toolbar --}}
        <div class="fw-toolbar" role="toolbar" aria-label="Editor toolbar">
            <div class="fw-toolbar__inner">

                {{-- Headings & Paragraph --}}
                <div class="fw-toolbar__group">
                    <select class="fw-toolbar__select" data-fw-action="heading" title="Heading" aria-label="Text style">
                        <option value="p">Paragraph</option>
                        <option value="h1">Heading 1</option>
                        <option value="h2">Heading 2</option>
                        <option value="h3">Heading 3</option>
                        <option value="h4">Heading 4</option>
                        <option value="h5">Heading 5</option>
                        <option value="h6">Heading 6</option>
                    </select>
                </div>

                <div class="fw-toolbar__divider"></div>

                {{-- Formatting --}}
                <div class="fw-toolbar__group">
                    <button type="button" class="fw-toolbar__btn" data-fw-action="bold" title="Bold (Ctrl+B)" aria-label="Bold">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn" data-fw-action="italic" title="Italic (Ctrl+I)" aria-label="Italic">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn" data-fw-action="underline" title="Underline (Ctrl+U)" aria-label="Underline">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3"/><line x1="4" y1="21" x2="20" y2="21"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn" data-fw-action="strikethrough" title="Strikethrough" aria-label="Strikethrough">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4H9a3 3 0 0 0-2.83 4"/><path d="M14 12a4 4 0 0 1 0 8H6"/><line x1="4" y1="12" x2="20" y2="12"/></svg>
                    </button>
                </div>

                <div class="fw-toolbar__divider"></div>

                {{-- Lists & Blocks --}}
                <div class="fw-toolbar__group">
                    <button type="button" class="fw-toolbar__btn" data-fw-action="insertUnorderedList" title="Bullet list" aria-label="Bullet list">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3" cy="6" r="1" fill="currentColor" stroke="none"/><circle cx="3" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="3" cy="18" r="1" fill="currentColor" stroke="none"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn" data-fw-action="insertOrderedList" title="Numbered list" aria-label="Numbered list">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1.5"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn" data-fw-action="blockquote" title="Blockquote" aria-label="Blockquote">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>
                    </button>
                </div>

                <div class="fw-toolbar__divider"></div>

                {{-- Code --}}
                <div class="fw-toolbar__group">
                    <button type="button" class="fw-toolbar__btn" data-fw-action="inlineCode" title="Inline code" aria-label="Inline code">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn" data-fw-action="codeBlock" title="Code block" aria-label="Code block">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    </button>
                </div>

                <div class="fw-toolbar__divider"></div>

                {{-- Link & Image --}}
                <div class="fw-toolbar__group">
                    <button type="button" class="fw-toolbar__btn" data-fw-action="insertLink" title="Insert link" aria-label="Insert link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn" data-fw-action="insertImage" title="Insert image" aria-label="Insert image">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    </button>
                </div>

                <div class="fw-toolbar__divider"></div>

                {{-- Alignment --}}
                <div class="fw-toolbar__group">
                    <button type="button" class="fw-toolbar__btn" data-fw-action="justifyLeft" title="Align left" aria-label="Align left">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="10" x2="3" y2="10"/><line x1="15" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="15" y1="18" x2="3" y2="18"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn" data-fw-action="justifyCenter" title="Align center" aria-label="Align center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="10" x2="3" y2="10"/><line x1="17" y1="6" x2="7" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="7" y2="18"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn" data-fw-action="justifyRight" title="Align right" aria-label="Align right">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="9" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="9" y2="18"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn" data-fw-action="justifyFull" title="Justify" aria-label="Justify">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="3" y2="18"/></svg>
                    </button>
                </div>

                <div class="fw-toolbar__divider"></div>

                {{-- History --}}
                <div class="fw-toolbar__group">
                    <button type="button" class="fw-toolbar__btn" data-fw-action="undo" title="Undo (Ctrl+Z)" aria-label="Undo">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn" data-fw-action="redo" title="Redo (Ctrl+Y)" aria-label="Redo">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 14 20 9 15 4"/><path d="M4 20v-7a4 4 0 0 1 4-4h12"/></svg>
                    </button>
                </div>

                {{-- Right-side actions --}}
                <div class="fw-toolbar__group fw-toolbar__group--right">
                    <button type="button" class="fw-toolbar__btn fw-toolbar__btn--icon-text" data-fw-action="togglePreview" title="Preview" aria-label="Toggle preview">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn fw-toolbar__btn--icon-text" data-fw-action="toggleSource" title="HTML source" aria-label="Toggle HTML source">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    </button>
                    <button type="button" class="fw-toolbar__btn fw-toolbar__btn--icon-text" data-fw-action="toggleFullscreen" title="Fullscreen" aria-label="Toggle fullscreen">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                    </button>
                </div>

            </div>

            {{-- Upload progress --}}
            <div class="fw-upload-progress" style="display:none" aria-live="polite">
                <div class="fw-upload-progress__bar"></div>
                <span class="fw-upload-progress__label">Uploading...</span>
            </div>
        </div>

        {{-- Editable Content Area --}}
        <div
            id="{{ $editorId }}-content"
            class="fw-editor__content"
            contenteditable="{{ $readonly ? 'false' : 'true' }}"
            data-placeholder="{{ $placeholder }}"
            style="min-height: {{ $height }}px"
            spellcheck="{{ config('flexwave-wysiwyg.defaults.spellcheck', true) ? 'true' : 'false' }}"
        ></div>

        {{-- Preview Panel --}}
        <div id="{{ $editorId }}-preview" class="fw-editor__preview" style="display:none; min-height: {{ $height }}px">
            <div class="fw-editor__preview-inner"></div>
        </div>

        {{-- Source View --}}
        <textarea
            id="{{ $editorId }}-source"
            class="fw-editor__source"
            style="display:none; min-height: {{ $height }}px"
            spellcheck="false"
        ></textarea>

        {{-- Status bar --}}
        <div class="fw-editor__statusbar">
            <span class="fw-statusbar__wordcount">0 words</span>
            <span class="fw-statusbar__charcount">0 characters</span>
            <span class="fw-statusbar__path"></span>
        </div>
    </div>

    {{-- Hidden file input for image upload --}}
    <input
        type="file"
        id="{{ $editorId }}-file-input"
        accept="image/jpeg,image/png,image/gif,image/webp"
        style="display:none"
        aria-hidden="true"
    >

    {{-- Validation error --}}
    @error($errorKey)
        <p class="fw-editor__error" role="alert">{{ $message }}</p>
    @enderror

    {{-- Link modal --}}
    <div id="{{ $editorId }}-link-modal" class="fw-modal" role="dialog" aria-modal="true" aria-label="Insert link" style="display:none">
        <div class="fw-modal__backdrop"></div>
        <div class="fw-modal__box">
            <div class="fw-modal__header">
                <h3 class="fw-modal__title">Insert Link</h3>
                <button type="button" class="fw-modal__close" aria-label="Close">&times;</button>
            </div>
            <div class="fw-modal__body">
                <label class="fw-modal__label">URL
                    <input type="url" class="fw-modal__input" data-field="href" placeholder="https://example.com">
                </label>
                <label class="fw-modal__label">Display text
                    <input type="text" class="fw-modal__input" data-field="text" placeholder="Link text">
                </label>
                <label class="fw-modal__label fw-modal__label--checkbox">
                    <input type="checkbox" data-field="blank"> Open in new tab
                </label>
            </div>
            <div class="fw-modal__footer">
                <button type="button" class="fw-btn fw-btn--ghost fw-modal__cancel">Cancel</button>
                <button type="button" class="fw-btn fw-btn--primary fw-modal__confirm">Insert</button>
            </div>
        </div>
    </div>
</div>
