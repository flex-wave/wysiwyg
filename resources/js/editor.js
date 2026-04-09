/*!
 * FlexWave WYSIWYG Editor — editor.js
 * FlexWave © 2024 — MIT License
 * Vanilla JS, zero dependencies
 */
(function (global) {
  'use strict';

  /* ──────────────────────────────────────────────
     Core FlexWaveEditor class
  ────────────────────────────────────────────── */
  class FlexWaveEditor {
    /**
     * @param {HTMLElement} wrapperEl  .fw-wysiwyg-wrapper
     * @param {object}      options
     */
    constructor(wrapperEl, options = {}) {
      this.wrapper   = wrapperEl;
      this.editorId  = wrapperEl.dataset.fwEditor;
      this.opts      = Object.assign({
        uploadUrl:   '/flexwave/upload',
        csrfToken:   '',
        height:      400,
        placeholder: 'Start writing…',
        darkMode:    'auto',
        readonly:    false,
        autofocus:   false,
        plugins:     [],
      }, options);

      this._mode = 'editor'; // 'editor' | 'preview' | 'source'
      this._isFullscreen = false;

      this._resolveElements();
      this._initContent();
      this._bindToolbar();
      this._bindKeyboard();
      this._bindContentEvents();
      this._loadPlugins();
      this._updateStatusBar();

      if (this.opts.autofocus) this.contentEl.focus();

      this._dispatch('fw:init', { editor: this });
    }

    /* ── Element References ── */
    _resolveElements() {
      const id = this.editorId;
      this.editorEl    = document.getElementById(id);
      this.contentEl   = document.getElementById(id + '-content');
      this.previewEl   = document.getElementById(id + '-preview');
      this.previewInner= this.previewEl?.querySelector('.fw-editor__preview-inner');
      this.sourceEl    = document.getElementById(id + '-source');
      this.textareaEl  = document.getElementById(id + '-input');
      this.fileInput   = document.getElementById(id + '-file-input');
      this.toolbar     = this.editorEl?.querySelector('.fw-toolbar__inner');
      this.progressWrap= this.editorEl?.querySelector('.fw-upload-progress');
      this.progressBar = this.editorEl?.querySelector('.fw-upload-progress__bar');
      this.wordCountEl = this.editorEl?.querySelector('.fw-statusbar__wordcount');
      this.charCountEl = this.editorEl?.querySelector('.fw-statusbar__charcount');
      this.pathEl      = this.editorEl?.querySelector('.fw-statusbar__path');
      this.linkModal   = document.getElementById(id + '-link-modal');
    }

    /* ── Init content from hidden textarea ── */
    _initContent() {
      if (this.textareaEl && this.contentEl) {
        const stored = this.textareaEl.value.trim();
        if (stored) {
          this.contentEl.innerHTML = stored;
        }
      }
    }

    /* ── Toolbar event delegation ── */
    _bindToolbar() {
      if (!this.toolbar) return;

      this.toolbar.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-fw-action]');
        if (!btn) return;
        e.preventDefault();
        this._handleAction(btn.dataset.fwAction, btn);
      });

      // Heading select
      const headingSelect = this.toolbar.querySelector('[data-fw-action="heading"]');
      if (headingSelect) {
        headingSelect.addEventListener('change', () => {
          const val = headingSelect.value;
          if (val === 'p') {
            document.execCommand('formatBlock', false, 'p');
          } else {
            document.execCommand('formatBlock', false, val);
          }
          this.contentEl.focus();
          this._syncTextarea();
        });
      }

      // File input for image upload
      if (this.fileInput) {
        this.fileInput.addEventListener('change', () => {
          if (this.fileInput.files[0]) {
            this._uploadImage(this.fileInput.files[0]);
            this.fileInput.value = '';
          }
        });
      }

      // Link modal bindings
      this._bindLinkModal();
    }

    /* ── Individual action handler ── */
    _handleAction(action, btn) {
      this.contentEl.focus();

      const execMap = {
        bold:              () => document.execCommand('bold'),
        italic:            () => document.execCommand('italic'),
        underline:         () => document.execCommand('underline'),
        strikethrough:     () => document.execCommand('strikeThrough'),
        insertUnorderedList: () => document.execCommand('insertUnorderedList'),
        insertOrderedList: () => document.execCommand('insertOrderedList'),
        justifyLeft:       () => document.execCommand('justifyLeft'),
        justifyCenter:     () => document.execCommand('justifyCenter'),
        justifyRight:      () => document.execCommand('justifyRight'),
        justifyFull:       () => document.execCommand('justifyFull'),
        undo:              () => document.execCommand('undo'),
        redo:              () => document.execCommand('redo'),
      };

      if (execMap[action]) {
        execMap[action]();
        this._updateActiveStates();
        this._syncTextarea();
        return;
      }

      switch (action) {
        case 'blockquote':
          this._toggleBlockquote();
          break;
        case 'inlineCode':
          this._insertInlineCode();
          break;
        case 'codeBlock':
          this._insertCodeBlock();
          break;
        case 'insertLink':
          this._openLinkModal();
          break;
        case 'insertImage':
          this.fileInput?.click();
          break;
        case 'togglePreview':
          this._togglePreview(btn);
          break;
        case 'toggleSource':
          this._toggleSource(btn);
          break;
        case 'toggleFullscreen':
          this._toggleFullscreen();
          break;
      }
    }

    /* ── Blockquote toggle ── */
    _toggleBlockquote() {
      const sel = window.getSelection();
      if (!sel || !sel.rangeCount) return;
      const node = sel.anchorNode;
      const bq   = node.nodeType === 1
        ? node.closest('blockquote')
        : node.parentElement?.closest('blockquote');

      if (bq) {
        // Unwrap
        const p = document.createElement('p');
        p.innerHTML = bq.innerHTML;
        bq.replaceWith(p);
      } else {
        document.execCommand('formatBlock', false, 'blockquote');
      }
      this._syncTextarea();
    }

    /* ── Inline code ── */
    _insertInlineCode() {
      const sel = window.getSelection();
      if (!sel || !sel.rangeCount) return;
      const text = sel.toString();
      const code = document.createElement('code');
      code.textContent = text || 'code';
      const range = sel.getRangeAt(0);
      range.deleteContents();
      range.insertNode(code);
      this._syncTextarea();
    }

    /* ── Code block ── */
    _insertCodeBlock() {
      const sel = window.getSelection();
      if (!sel || !sel.rangeCount) return;
      const text = sel.toString();
      const pre  = document.createElement('pre');
      const code = document.createElement('code');
      code.textContent = text || '// your code here';
      pre.appendChild(code);
      const range = sel.getRangeAt(0);
      range.deleteContents();
      range.insertNode(pre);
      // Move cursor after block
      const newRange = document.createRange();
      newRange.setStartAfter(pre);
      sel.removeAllRanges();
      sel.addRange(newRange);
      this._syncTextarea();
    }

    /* ── Link Modal ── */
    _bindLinkModal() {
      if (!this.linkModal) return;

      const backdrop = this.linkModal.querySelector('.fw-modal__backdrop');
      const closeBtn = this.linkModal.querySelector('.fw-modal__close');
      const cancelBtn= this.linkModal.querySelector('.fw-modal__cancel');
      const confirmBtn=this.linkModal.querySelector('.fw-modal__confirm');

      const close = () => { this.linkModal.style.display = 'none'; };

      backdrop?.addEventListener('click', close);
      closeBtn?.addEventListener('click', close);
      cancelBtn?.addEventListener('click', close);

      confirmBtn?.addEventListener('click', () => {
        const href = this.linkModal.querySelector('[data-field="href"]')?.value?.trim();
        const text = this.linkModal.querySelector('[data-field="text"]')?.value?.trim();
        const blank= this.linkModal.querySelector('[data-field="blank"]')?.checked;

        if (!href) return;

        if (this._savedRange) {
          const sel = window.getSelection();
          sel.removeAllRanges();
          sel.addRange(this._savedRange);
        }

        const displayText = text || href;
        const anchor = `<a href="${this._escAttr(href)}" ${blank ? 'target="_blank" rel="noopener noreferrer"' : ''}>${this._escHtml(displayText)}</a>`;
        document.execCommand('insertHTML', false, anchor);
        close();
        this._syncTextarea();
        this._dispatch('fw:linkInserted', { href, text: displayText, blank });
      });
    }

    _openLinkModal() {
      if (!this.linkModal) return;
      // Save current selection
      const sel = window.getSelection();
      this._savedRange = sel.rangeCount ? sel.getRangeAt(0).cloneRange() : null;

      const textInput = this.linkModal.querySelector('[data-field="text"]');
      const hrefInput = this.linkModal.querySelector('[data-field="href"]');
      if (textInput) textInput.value = sel.toString() || '';
      if (hrefInput) hrefInput.value = '';

      this.linkModal.style.display = 'flex';
      hrefInput?.focus();
    }

    /* ── Image Upload ── */
    _uploadImage(file) {
      if (!this.opts.uploadUrl) return;

      const formData = new FormData();
      formData.append('file', file);

      this._showProgress(true);
      this._dispatch('fw:uploadStart', { file });

      fetch(this.opts.uploadUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': this.opts.csrfToken,
          'Accept': 'application/json',
        },
        body: formData,
      })
        .then(r => r.json())
        .then(data => {
          this._showProgress(false);
          if (data.success) {
            const img = `<img src="${this._escAttr(data.url)}" alt="" loading="lazy">`;
            document.execCommand('insertHTML', false, img);
            this._syncTextarea();
            this._dispatch('fw:uploadSuccess', { url: data.url, path: data.path });
          } else {
            this._dispatch('fw:uploadError', { message: data.message });
            console.error('[FlexWave] Upload error:', data.message);
          }
        })
        .catch(err => {
          this._showProgress(false);
          this._dispatch('fw:uploadError', { error: err });
          console.error('[FlexWave] Upload failed:', err);
        });
    }

    _showProgress(show) {
      if (this.progressWrap) {
        this.progressWrap.style.display = show ? 'flex' : 'none';
      }
    }

    /* ── Preview / Source / Fullscreen ── */
    _togglePreview(btn) {
      if (this._mode === 'preview') {
        this._setMode('editor');
        if (btn) { btn.setAttribute('aria-pressed', 'false'); btn.classList.remove('fw--active'); }
      } else {
        this._setMode('preview');
        if (btn) { btn.setAttribute('aria-pressed', 'true'); btn.classList.add('fw--active'); }
        // Deactivate source
        const srcBtn = this.toolbar?.querySelector('[data-fw-action="toggleSource"]');
        if (srcBtn) { srcBtn.setAttribute('aria-pressed', 'false'); srcBtn.classList.remove('fw--active'); }
      }
    }

    _toggleSource(btn) {
      if (this._mode === 'source') {
        // Apply changes from source back to content
        this.contentEl.innerHTML = this.sourceEl.value;
        this._setMode('editor');
        if (btn) { btn.setAttribute('aria-pressed', 'false'); btn.classList.remove('fw--active'); }
      } else {
        this._setMode('source');
        if (btn) { btn.setAttribute('aria-pressed', 'true'); btn.classList.add('fw--active'); }
        const preBtn = this.toolbar?.querySelector('[data-fw-action="togglePreview"]');
        if (preBtn) { preBtn.setAttribute('aria-pressed', 'false'); preBtn.classList.remove('fw--active'); }
      }
    }

    _setMode(mode) {
      this._mode = mode;
      const showContent = mode === 'editor';
      const showPreview = mode === 'preview';
      const showSource  = mode === 'source';

      if (this.contentEl) this.contentEl.style.display = showContent ? '' : 'none';
      if (this.previewEl) this.previewEl.style.display  = showPreview ? '' : 'none';
      if (this.sourceEl)  this.sourceEl.style.display   = showSource  ? '' : 'none';

      if (showPreview && this.previewInner) {
        this.previewInner.innerHTML = this.contentEl.innerHTML;
      }
      if (showSource && this.sourceEl) {
        this.sourceEl.value = this._formatHtml(this.contentEl.innerHTML);
      }
    }

    _toggleFullscreen() {
      this._isFullscreen = !this._isFullscreen;
      this.editorEl?.classList.toggle('fw-editor--fullscreen', this._isFullscreen);
      document.body.style.overflow = this._isFullscreen ? 'hidden' : '';
      this._dispatch('fw:fullscreen', { active: this._isFullscreen });
    }

    /* ── Keyboard Shortcuts ── */
    _bindKeyboard() {
      if (!this.contentEl) return;

      this.contentEl.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this._isFullscreen) {
          this._toggleFullscreen();
        }

        // Tab in code blocks
        if (e.key === 'Tab') {
          const sel = window.getSelection();
          const node = sel?.anchorNode;
          const inPre = node?.nodeType === 3
            ? node.parentElement?.closest('pre')
            : node?.closest?.('pre');
          if (inPre) {
            e.preventDefault();
            document.execCommand('insertText', false, '  ');
          }
        }

        // Path breadcrumb
        this._updatePath(e);
      });

      // Drag & drop images
      this.contentEl.addEventListener('drop', (e) => {
        const files = Array.from(e.dataTransfer?.files || [])
          .filter(f => f.type.startsWith('image/'));
        if (files.length) {
          e.preventDefault();
          files.forEach(f => this._uploadImage(f));
        }
      });

      this.contentEl.addEventListener('paste', (e) => {
        const items = Array.from(e.clipboardData?.items || []);
        const imgItem = items.find(i => i.type.startsWith('image/'));
        if (imgItem) {
          e.preventDefault();
          this._uploadImage(imgItem.getAsFile());
        }
      });
    }

    /* ── Content change events ── */
    _bindContentEvents() {
      if (!this.contentEl) return;

      const sync = () => {
        this._syncTextarea();
        this._updateStatusBar();
        this._updateActiveStates();
      };

      this.contentEl.addEventListener('input',   sync);
      this.contentEl.addEventListener('keyup',   () => this._updateActiveStates());
      this.contentEl.addEventListener('mouseup', () => this._updateActiveStates());
      this.contentEl.addEventListener('focus',   () => this._updateActiveStates());

      // Source textarea → sync on input
      this.sourceEl?.addEventListener('input', () => {
        this.contentEl.innerHTML = this.sourceEl.value;
        this._syncTextarea();
        this._updateStatusBar();
      });
    }

    /* ── Sync content to hidden textarea ── */
    _syncTextarea() {
      if (this.textareaEl && this.contentEl) {
        this.textareaEl.value = this.contentEl.innerHTML;
        this._dispatch('fw:change', { html: this.textareaEl.value });
      }
    }

    /* ── Status Bar ── */
    _updateStatusBar() {
      if (!this.contentEl) return;
      const text  = this.contentEl.innerText || '';
      const words = text.trim() ? text.trim().split(/\s+/).length : 0;
      const chars = text.length;
      if (this.wordCountEl) this.wordCountEl.textContent = `${words} word${words !== 1 ? 's' : ''}`;
      if (this.charCountEl) this.charCountEl.textContent = `${chars} char${chars !== 1 ? 's' : ''}`;
    }

    _updatePath(e) {
      if (!this.pathEl) return;
      try {
        const sel = window.getSelection();
        if (!sel || !sel.rangeCount) return;
        let node = sel.anchorNode;
        if (node.nodeType === 3) node = node.parentElement;
        const path = [];
        while (node && node !== this.contentEl) {
          path.unshift(node.tagName.toLowerCase());
          node = node.parentElement;
        }
        this.pathEl.textContent = path.join(' › ');
      } catch {}
    }

    /* ── Active state for toolbar buttons ── */
    _updateActiveStates() {
      if (!this.toolbar) return;

      const stateMap = {
        bold:                'bold',
        italic:              'italic',
        underline:           'underline',
        strikethrough:       'strikeThrough',
        insertUnorderedList: 'insertUnorderedList',
        insertOrderedList:   'insertOrderedList',
        justifyLeft:         'justifyLeft',
        justifyCenter:       'justifyCenter',
        justifyRight:        'justifyRight',
        justifyFull:         'justifyFull',
      };

      for (const [action, cmd] of Object.entries(stateMap)) {
        const btn = this.toolbar.querySelector(`[data-fw-action="${action}"]`);
        if (btn) {
          const active = document.queryCommandState(cmd);
          btn.classList.toggle('fw--active', active);
          btn.setAttribute('aria-pressed', String(active));
        }
      }

      // Heading select
      const headingSelect = this.toolbar.querySelector('[data-fw-action="heading"]');
      if (headingSelect) {
        const block = document.queryCommandValue('formatBlock').toLowerCase();
        headingSelect.value = ['h1','h2','h3','h4','h5','h6'].includes(block) ? block : 'p';
      }
    }

    /* ── Plugin Loader ── */
    _loadPlugins() {
      if (!Array.isArray(this.opts.plugins)) return;
      this.opts.plugins.forEach(src => {
        const script = document.createElement('script');
        script.src   = src;
        script.defer = true;
        script.onload = () => {
          this._dispatch('fw:pluginLoaded', { src });
        };
        document.head.appendChild(script);
      });
    }

    /* ── Event Dispatch ── */
    _dispatch(name, detail = {}) {
      const event = new CustomEvent(name, {
        bubbles: true,
        detail:  Object.assign({ editorId: this.editorId }, detail),
      });
      this.wrapper.dispatchEvent(event);
    }

    /* ── Utilities ── */
    _escAttr(str) {
      return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    _escHtml(str) {
      const d = document.createElement('div');
      d.textContent = str;
      return d.innerHTML;
    }

    _formatHtml(html) {
      // Very simple pretty-printer
      let formatted = '';
      let indent = 0;
      html.replace(/>\s*</g, '>\n<').split('\n').forEach(line => {
        line = line.trim();
        if (!line) return;
        if (/^<\//.test(line)) indent = Math.max(0, indent - 1);
        formatted += '  '.repeat(indent) + line + '\n';
        if (/^<[^/!][^>]*[^/]>$/.test(line) && !/^<(br|hr|img|input)/.test(line)) {
          indent++;
        }
      });
      return formatted.trim();
    }

    /* ── Public API ── */
    getHTML() {
      return this.contentEl?.innerHTML ?? '';
    }

    setHTML(html) {
      if (this.contentEl) {
        this.contentEl.innerHTML = html;
        this._syncTextarea();
        this._updateStatusBar();
      }
    }

    clear() {
      this.setHTML('');
    }

    focus() {
      this.contentEl?.focus();
    }

    setDarkMode(mode) {
      if (this.editorEl) {
        this.editorEl.dataset.darkMode = mode;
      }
    }
  }

  /* ──────────────────────────────────────────────
     Auto-init all editors on DOMContentLoaded
  ────────────────────────────────────────────── */
  function initAll() {
    document.querySelectorAll('[data-fw-editor]').forEach(wrapper => {
      if (wrapper.__fwEditor) return; // already initialized
      try {
        const rawOpts = wrapper.dataset.fwOptions;
        const options = rawOpts ? JSON.parse(rawOpts) : {};
        wrapper.__fwEditor = new FlexWaveEditor(wrapper, options);
      } catch (err) {
        console.error('[FlexWave] Editor init error:', err);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  /* ── Global API ── */
  global.FlexWaveEditor = FlexWaveEditor;
  global.FlexWave = {
    /**
     * Get the editor instance for a given wrapper element or editor-id string.
     * @param {string|HTMLElement} target
     * @returns {FlexWaveEditor|null}
     */
    getInstance(target) {
      const el = typeof target === 'string'
        ? document.querySelector(`[data-fw-editor="${target}"]`)
        : target;
      return el?.__fwEditor ?? null;
    },

    init: initAll,
  };

})(window);
