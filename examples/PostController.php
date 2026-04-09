<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use FlexWave\Wysiwyg\Facades\Wysiwyg;

/**
 * Example PostController demonstrating FlexWave WYSIWYG integration.
 */
class PostController extends Controller
{
    public function create(): View
    {
        return view('posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string|min:10',
            'category' => 'nullable|string|in:news,tutorial,opinion',
        ]);

        // Sanitize the HTML from the editor before storing it.
        // This strips disallowed tags and blocks javascript: URIs.
        $validated['content'] = Wysiwyg::sanitize($validated['content']);

        // You can also generate an excerpt for meta/preview use:
        $validated['excerpt'] = Wysiwyg::excerpt($validated['content'], 200);

        Post::create($validated);

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post published successfully!');
    }

    public function edit(Post $post): View
    {
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string|min:10',
            'category' => 'nullable|string|in:news,tutorial,opinion',
        ]);

        $validated['content'] = Wysiwyg::sanitize($validated['content']);
        $validated['excerpt'] = Wysiwyg::excerpt($validated['content'], 200);

        $post->update($validated);

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post updated!');
    }
}
