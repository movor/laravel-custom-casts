<?php

namespace Movor\LaravelCustomCasts\Test\Support\Models;

class ImageWithInvalidCustomCast extends Image
{
    protected $customCasts = [
        'image' => \stdClass::class,
    ];
}