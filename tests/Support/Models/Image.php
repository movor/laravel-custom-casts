<?php

namespace Movor\LaravelCustomCasts\Test\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Movor\LaravelCustomCasts\CustomCastableTrait;
use Movor\LaravelCustomCasts\Test\Support\CustomCasts\Base64ImageCast;

class Image extends Model
{
    use CustomCastableTrait;

    protected $guarded = [];
    protected $table = 'images';

    protected $casts = [
        'data' => 'array'
    ];

    protected $customCasts = [
        'image' => Base64ImageCast::class,
    ];
}