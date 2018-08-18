# Make your own cast type for Laravel model

[![Build](https://api.travis-ci.org/movor/laravel-custom-casts.svg?branch=master)](https://travis-ci.org/movor/laravel-custom-casts)
[![Downloads](https://poser.pugx.org/movor/laravel-custom-casts/downloads)](https://packagist.org/packages/movor/laravel-custom-casts)
[![Stable](https://poser.pugx.org/movor/laravel-custom-casts/v/stable)](https://packagist.org/packages/movor/laravel-custom-casts)
[![License](https://poser.pugx.org/movor/laravel-custom-casts/license)](https://packagist.org/packages/movor/laravel-custom-casts)

By default, from version 5 Laravel supports attribute casting. If we define cast property on our model, Laravel will help us convert defined attributes to common data types. Currently supported cast types (Laravel 5.6) are: integer, real, float, double, string, boolean, object, array,
collection,date, datetime and timestamp.

If those default cast types are not enough and you want to make your own, this Laravel package is here to help you accomplish that.

***

## Compatibility

The package is compatible with Laravel versions 5.5.* and 5.6.*

## Installation

Install the package via composer:

```bash
composer require movor/laravel-custom-casts
```

## Casting User Image Example

Assume that we have user model with "image" field (varchar 255 in database).

We'll need to add custom cast trait to our user model, and link it to the class that'll handle casting.
So, let's edit user model file which is included in Laravel by default.

```php
// File: app/User.php

// ...

protected $casts = [
    'image' => ImageCast::class
];

// ...
```

Next step is to create class that'll handle casting. It must implement "setAttribute" method which will handle transforming initial value (passed to model) and any other logic involved.

```php
// File: app/CustomCasts/ImageCast.php

namespace App\CustomCasts;

use Movor\LaravelCustomCasts\CustomCastableBase;
use Illuminate\Http\UploadedFile;

class ImageCast extends CustomCastableBase
{
    public function setAttribute(UploadedFile $file)
    {
        // Define storage dir
        // (relative to "storage/app" folder in Laravel project)
        $storageDir = 'images';

        // Generate random image name
        $filename = str_random() . '.' . $file->extension();

        // Save image
        $file->storeAs($storageDir, $filename);

        // This will be stored in db field: "image"
        return $storageDir . '/' . $filename;
    }
}
```

Let's jump to user creation example. This will trigger our custom cast logic.
We'll edit user creation method that can be found in Laravel default project.

```php
// File: app/Http/Controllers/Auth/RegisterController.php

// ...

protected function create(array $data)
{
    return User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => bcrypt($data['password']),
        // Past the whole Illuminate\Http\UploadedFile object,
        // we'll handle it in our custom cast class
        'image' => $data['image']
    ]);
}

// ...
```

Ok, now we have our user created and user image stored.

But we should also handle deleting image when user is deleted. This can be accomplished by utilizing underlying eloquent events handling. Each time eloquent event is fired, it'll look up for public method with the same name in our custom cast class.

Possible method names are:
retrieved, creating, created, updating, updated, saving, saved,  deleting, deleted, restoring, restored.

```php
// File: app/CustomCasts/ImageCast.php

// ...

public function deleted()
{
    // We can access underlying model with $this->model
    // and attribute name that is being casted with $this->attribute

    // Retrieve image path and delete it from the disk
    $imagePath = $this->model->image;
    Storage::delete($imagePath);
}

// ...

```

This should cover the basics on usage of custom casts.