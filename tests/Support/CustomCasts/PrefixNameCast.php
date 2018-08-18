<?php

namespace Movor\LaravelCustomCasts\Test\Support\CustomCasts;

use Movor\LaravelCustomCasts\CustomCastBase;

class PrefixNameCast extends CustomCastBase
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