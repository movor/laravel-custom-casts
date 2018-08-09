<?php

namespace Movor\LaravelCustomCasts;

use Illuminate\Database\Eloquent\Model;

abstract class CustomCastableBase
{
    /**
     * Model
     *
     * @var Model
     */
    protected $model;

    /**
     * Corresponding db field (model attribute name)
     *
     * @var string
     */
    protected $attribute;

    /**
     * Set model
     *
     * @param Model $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Set model field name which is going to be custom casted
     *
     * @param $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * Enforce implementation in child classes
     *
     * Intercept value passed to model under specified field ($attribute)
     * and change it to our will
     *
     * @param mixed $value Default value passed to model attribute
     *
     * @return mixed
     */
    abstract public function castAttribute($value);
}