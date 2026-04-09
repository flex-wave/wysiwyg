{{--
|--------------------------------------------------------------------------
| EXAMPLE: resources/views/posts/create.blade.php
|--------------------------------------------------------------------------
| A complete example of how to use the FlexWave WYSIWYG editor
| in a real Laravel project.
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>

    {{--
        Option A: Use the Blade directive (after php artisan vendor:publish)
    --}}
    @wysiwygStyles

    {{--
        Option B: Direct asset links
        <link rel="stylesheet" href="{{ asset('vendor/flexwave-wysiwyg/css/editor.css') }}">
    --}}
</head>
<body>

<div class="container" style="max-width:860px; margin:40px auto; padding:0 20px; font-family:system-ui,sans-serif">

    <h1 style="margin-bottom:8px">Create New Post</h1>

    {{-- Validation summary --}}
    @if ($errors->any())
        <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:6px; padding:12px 16px; margin-bottom:20px">
            <ul style="margin:0; padding-left:20px; color:#b91c1c">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('posts.store') }}">
        @csrf

        {{-- Title field --}}
        <div style="margin-bottom:20px">
            <label for="title" style="display:block; font-weight:600; margin-bottom:6px">
                Title <span style="color:#ef4444">*</span>
            </label>
            <input
                id="title"
                name="title"
                type="text"
                value="{{ old('title') }}"
                placeholder="Post title"
                style="width:100%; height:40px; padding:0 12px; border:1.5px solid #e2e2ec; border-radius:6px; font-size:15px; box-sizing:border-box"
            >
        </div>

        {{-- WYSIWYG Editor —————————————————————————— --}}
        <div style="margin-bottom:20px">
            <label style="display:block; font-weight:600; margin-bottom:8px">
                Content <span style="color:#ef4444">*</span>
            </label>

            {{--
                Minimum required attributes:
                  name    = the form field name
                  :value  = old() for repopulation after validation

                Optional attributes:
                  placeholder = custom placeholder text
                  :height     = minimum editor height in pixels
                  dark-mode   = 'auto' | 'light' | 'dark'
                  :required   = adds required attribute to the hidden textarea
                  :readonly   = makes the editor read-only
                  id          = custom HTML id (auto-generated if omitted)
                  class       = extra CSS classes on the wrapper div
            --}}
            <x-flexwave-editor
                name="content"
                :value="old('content', $post->content ?? '')"
                placeholder="Tell your story..."
                :height="450"
                dark-mode="auto"
                :required="true"
            />
        </div>
        {{-- ————————————————————————————————————————— --}}

        {{-- Category --}}
        <div style="margin-bottom:20px">
            <label for="category" style="display:block; font-weight:600; margin-bottom:6px">Category</label>
            <select
                id="category"
                name="category"
                style="height:40px; padding:0 10px; border:1.5px solid #e2e2ec; border-radius:6px; min-width:200px"
            >
                <option value="">Select a category</option>
                <option value="news"      {{ old('category') === 'news' ? 'selected' : '' }}>News</option>
                <option value="tutorial"  {{ old('category') === 'tutorial' ? 'selected' : '' }}>Tutorial</option>
                <option value="opinion"   {{ old('category') === 'opinion' ? 'selected' : '' }}>Opinion</option>
            </select>
        </div>

        {{-- Actions --}}
        <div style="display:flex; gap:10px">
            <button
                type="submit"
                style="height:40px; padding:0 24px; background:#6366f1; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer; font-size:14px"
            >
                Publish Post
            </button>
            <a
                href="{{ route('posts.index') }}"
                style="height:40px; padding:0 20px; display:inline-flex; align-items:center; border:1.5px solid #e2e2ec; border-radius:6px; color:#555; text-decoration:none; font-size:14px"
            >
                Cancel
            </a>
        </div>
    </form>

    {{-- Live word-count demo via JS API --}}
    <p id="live-count" style="margin-top:16px; font-size:13px; color:#888"></p>
</div>

{{-- JS Assets --}}
@wysiwygScripts

<script>
    // Access the editor instance after DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        // Find the wrapper by attribute (name === 'content')
        const wrapper = document.querySelector('[data-fw-editor]');
        if (!wrapper) return;

        // Listen for editor events
        wrapper.addEventListener('fw:init', ({ detail }) => {
            console.log('FlexWave editor ready:', detail.editorId);
        });

        wrapper.addEventListener('fw:change', ({ detail }) => {
            const editor = FlexWave.getInstance(detail.editorId);
            if (editor) {
                const words = editor.getHTML()
                    .replace(/<[^>]+>/g, ' ')
                    .trim()
                    .split(/\s+/)
                    .filter(Boolean).length;
                document.getElementById('live-count').textContent =
                    `Live count: ${words} word${words !== 1 ? 's' : ''}`;
            }
        });

        wrapper.addEventListener('fw:uploadSuccess', ({ detail }) => {
            console.log('Image uploaded:', detail.url);
        });
    });
</script>

</body>
</html>
