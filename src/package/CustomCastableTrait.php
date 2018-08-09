<?php

namespace Movor\LaravelCustomCasts;

trait CustomCastableTrait
{
    /**
     * Each field which is going to be custom casted
     * will have its own custom cast instance in this array
     *
     * @var array
     */
    protected $customCastObjects = [];

    /**
     * Boot trait
     */
    public static function bootCustomCastableTrait()
    {
        // Enable custom cast classes to listen to model events
        \Event::listen('eloquent.*: ' . get_called_class(), function ($event, $data) {
            $eventName = explode('.', explode(':', $event)[0])[1];

            /** @var self $model */
            $model = $data[0];

            if (!isset($model->customCasts)) {
                self::throwInvalidCustomCastException($model);
            }

            foreach ($model->customCasts as $key => $value) {
                $customCastObject = $model->getCustomCastObject($key);

                if (method_exists($customCastObject, $eventName)) {
                    $customCastObject->$eventName();
                }
            }
        });
    }

    /**
     * Hook into setAttribute logic and enable our custom cast do the job.
     *
     * This method is will override method in HasAttributes trait.
     *
     * @param $attribute
     * @param $value
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function setAttribute($attribute, $value)
    {
        if (// Handle defined mutators in object and
            // prioritize them against custom castable
            !$this->hasSetMutator($attribute) &&

            // Check if there is custom casts for this attribute
            isset($this->customCasts) && array_key_exists($attribute, $this->customCasts)
        ) {
            $customCastObject = $this->getCustomCastObject($attribute);

            // Cast attribute according to logic from custom cast object
            $this->attributes[$attribute] = $customCastObject->setAttribute($value);

            return $this;
        }

        return parent::setAttribute($attribute, $value);
    }

    /**
     * Lazy load custom cast object and return it
     *
     * @param $attribute
     *
     * @throws \Exception
     *
     * @return CustomCastableBase
     */
    protected function getCustomCastObject($attribute)
    {
        // Check if custom cast object already been set
        if (isset($this->customCastObjects[$attribute])) {
            return $this->customCastObjects[$attribute];
        }

        // Check if custom casts property exists for this attribute
        if (isset($this->customCasts) && array_key_exists($attribute, $this->customCasts)) {
            $customCastClass = $this->customCasts[$attribute];

            // Make sure that defined custom cast class is correct
            if (!is_subclass_of($customCastClass, CustomCastableBase::class)) {
                $message = "Custom cast class for '$attribute' needs to be extended from ";
                $message .= CustomCastableBase::class;

                throw new \Exception($message);
            }

            $customCastObject = new $customCastClass($this, $attribute);
            return $this->customCastObjects[$attribute] = $customCastObject;
        }

        self::throwInvalidCustomCastException($this);
    }

    /**
     * @param $model
     *
     * @throws \Exception
     */
    public static function throwInvalidCustomCastException($model)
    {
        $trait = CustomCastableTrait::class;
        $model = self::class;
        $message = "Model class '$model' which uses '$trait' needs to have 'customCast' array property defined";

        throw new \Exception($message);
    }
}