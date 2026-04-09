<?php

use Illuminate\Support\Facades\Route;
use FlexWave\Wysiwyg\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| FlexWave WYSIWYG Package Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the WysiwygServiceProvider within a group
| that uses the configured prefix and middleware.
|
*/

Route::post('/upload', [UploadController::class, 'upload'])
    ->name('upload');

Route::delete('/upload', [UploadController::class, 'delete'])
    ->name('upload.delete');
