<?php

namespace Movor\LaravelCustomCasts\Test\Support\CustomCasts;

use Movor\LaravelCustomCasts\CustomCastableBase;

class PrefixNameCast extends CustomCastableBase
{
    public function setAttribute($value)
    {
        return $value;
    }

    public function castAttribute($value)
    {
        return 'casted_' . $value;
    }
}