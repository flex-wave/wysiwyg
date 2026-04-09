<?php

namespace FlexWave\Wysiwyg\Events;

use Illuminate\Foundation\Auth\User;
use Illuminate\Queue\SerializesModels;

class ImageDeleted
{
    use SerializesModels;

    public function __construct(
        public readonly string $path,
        public readonly string $disk,
        public readonly ?User  $user = null,
    ) {}
}
