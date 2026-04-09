<?php

namespace FlexWave\Wysiwyg\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use FlexWave\Wysiwyg\Events\ImageUploaded;
use FlexWave\Wysiwyg\Events\ImageDeleted;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

class UploadController extends Controller
{
    /**
     * Handle image upload from the WYSIWYG editor.
     */
    public function upload(Request $request): JsonResponse
    {
        $config = config('flexwave-wysiwyg');

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . ($config['upload']['max_size'] ?? 5120),
                'mimetypes:' . implode(',', $config['upload']['allowed'] ?? ['image/jpeg', 'image/png', 'image/gif', 'image/webp']),
            ],
        ]);

        $file  = $request->file('file');
        $disk  = $config['upload']['disk'] ?? 'public';
        $path  = $config['upload']['path'] ?? 'wysiwyg/uploads';

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . '.' . $extension;
        $fullPath  = $path . '/' . $filename;

        try {
            $contents = $this->processImage($file, $config);
            Storage::disk($disk)->put($fullPath, $contents);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }

        $url = Storage::disk($disk)->url($fullPath);

        // Dispatch event
        $eventClass = $config['events']['image_uploaded'] ?? null;
        if ($eventClass && class_exists($eventClass)) {
            event(new $eventClass($fullPath, $url, $disk, auth()->user()));
        }

        return response()->json([
            'success'  => true,
            'url'      => $url,
            'path'     => $fullPath,
            'filename' => $filename,
        ]);
    }

    /**
     * Delete an uploaded image.
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $config = config('flexwave-wysiwyg');
        $disk   = $config['upload']['disk'] ?? 'public';
        $path   = $request->input('path');

        // Security: ensure the path is within the configured upload directory
        $allowedPrefix = $config['upload']['path'] ?? 'wysiwyg/uploads';
        if (! str_starts_with($path, $allowedPrefix)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);

            $eventClass = $config['events']['image_deleted'] ?? null;
            if ($eventClass && class_exists($eventClass)) {
                event(new $eventClass($path, $disk, auth()->user()));
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Process and optionally resize the image.
     */
    protected function processImage($file, array $config): string
    {
        $resize = $config['image_resize'] ?? [];

        if (! ($resize['enabled'] ?? true)) {
            return file_get_contents($file->getRealPath());
        }

        $maxWidth  = $resize['max_width'] ?? 1920;
        $maxHeight = $resize['max_height'] ?? 1080;
        $quality   = $resize['quality'] ?? 85;

        $image = Image::read($file->getRealPath());

        // Only resize if larger than max dimensions
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->scaleDown($maxWidth, $maxHeight);
        }

        return $image->toJpeg($quality)->toString();
    }
}
