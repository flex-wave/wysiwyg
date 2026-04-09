<?php

namespace FlexWave\Wysiwyg\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Illuminate\Support\Str;

class Editor extends Component
{
    /**
     * Unique editor instance ID.
     */
    public string $editorId;

    /**
     * JSON-encoded toolbar configuration.
     */
    public string $toolbarJson;

    /**
     * JSON-encoded editor options.
     */
    public string $optionsJson;

    /**
     * The upload route URL.
     */
    public string $uploadUrl;

    /**
     * Whether dark mode is enabled.
     */
    public string $darkMode;

    public function __construct(
        public string $name        = 'content',
        public string $value       = '',
        public string $placeholder = '',
        public int    $height      = 0,
        public bool   $required    = false,
        public bool   $readonly    = false,
        public bool   $autofocus   = false,
        public string $id          = '',
        public string $class       = '',
        string        $darkMode    = '',
    ) {
        $config = config('flexwave-wysiwyg', []);

        $this->editorId   = $this->id ?: 'fw-editor-' . Str::random(8);
        $this->uploadUrl  = route('flexwave-wysiwyg.upload');
        $this->darkMode   = $darkMode ?: ($config['defaults']['dark_mode'] ?? 'auto');

        if ($this->height === 0) {
            $this->height = $config['defaults']['height'] ?? 400;
        }

        if ($this->placeholder === '') {
            $this->placeholder = $config['defaults']['placeholder'] ?? 'Start writing here...';
        }

        $this->toolbarJson = json_encode($config['toolbar'] ?? []);
        $this->optionsJson = json_encode([
            'uploadUrl'   => $this->uploadUrl,
            'csrfToken'   => csrf_token(),
            'toolbar'     => $config['toolbar'] ?? [],
            'height'      => $this->height,
            'placeholder' => $this->placeholder,
            'darkMode'    => $this->darkMode,
            'readonly'    => $this->readonly,
            'autofocus'   => $this->autofocus,
            'plugins'     => $config['plugins'] ?? [],
        ]);
    }

    public function render(): View
    {
        return view('flexwave-wysiwyg::components.editor');
    }
}
