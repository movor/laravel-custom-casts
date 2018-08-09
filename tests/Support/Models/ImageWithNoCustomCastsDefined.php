<?php

namespace Movor\LaravelCustomCasts\Test\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Movor\LaravelCustomCasts\CustomCastableTrait;

class ImageWithNoCustomCastsDefined extends Model
{
    use CustomCastableTrait;

    protected $table = 'images';
}