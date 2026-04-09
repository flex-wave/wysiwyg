<?php

namespace FlexWave\Wysiwyg\Events;

use Illuminate\Foundation\Auth\User;
use Illuminate\Queue\SerializesModels;

class ImageUploaded
{
    use SerializesModels;

    public function __construct(
        public readonly string $path,
        public readonly string $url,
        public readonly string $disk,
        public readonly ?User  $user = null,
    ) {}
}
