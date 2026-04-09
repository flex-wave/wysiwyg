<?php

namespace FlexWave\Wysiwyg\Tests;

use FlexWave\Wysiwyg\WysiwygServiceProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;

class UploadControllerTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [WysiwygServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('flexwave-wysiwyg.upload.disk', 'testing');
        $app['config']->set('flexwave-wysiwyg.image_resize.enabled', false);
        $app['config']->set('flexwave-wysiwyg.middleware', ['web']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('testing');
    }

    public function test_upload_requires_file(): void
    {
        $response = $this->postJson(route('flexwave-wysiwyg.upload'), []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_upload_accepts_image(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $response = $this->postJson(route('flexwave-wysiwyg.upload'), ['file' => $file]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNotEmpty($response->json('url'));
        $this->assertNotEmpty($response->json('path'));
    }

    public function test_upload_rejects_non_image(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson(route('flexwave-wysiwyg.upload'), ['file' => $file]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_delete_requires_path(): void
    {
        $response = $this->deleteJson(route('flexwave-wysiwyg.upload.delete'), []);
        $response->assertStatus(422);
    }

    public function test_delete_blocks_path_traversal(): void
    {
        $response = $this->deleteJson(route('flexwave-wysiwyg.upload.delete'), [
            'path' => '../../../etc/passwd',
        ]);

        $response->assertStatus(403);
    }

    public function test_delete_removes_file(): void
    {
        // First upload a file
        $file = UploadedFile::fake()->image('photo.jpg');
        $uploadResponse = $this->postJson(route('flexwave-wysiwyg.upload'), ['file' => $file]);
        $path = $uploadResponse->json('path');

        // Now delete it
        $deleteResponse = $this->deleteJson(route('flexwave-wysiwyg.upload.delete'), [
            'path' => $path,
        ]);

        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);
        Storage::disk('testing')->assertMissing($path);
    }
}
