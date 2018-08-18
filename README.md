# Make your own cast type for Laravel model

[![Build](https://api.travis-ci.org/movor/laravel-custom-casts.svg?branch=master)](https://travis-ci.org/movor/laravel-custom-casts)
[![Downloads](https://poser.pugx.org/movor/laravel-custom-casts/downloads)](https://packagist.org/packages/movor/laravel-custom-casts)
[![Stable](https://poser.pugx.org/movor/laravel-custom-casts/v/stable)](https://packagist.org/packages/movor/laravel-custom-casts)
[![License](https://poser.pugx.org/movor/laravel-custom-casts/license)](https://packagist.org/packages/movor/laravel-custom-casts)

By default, from version 5 Laravel supports attribute casting. If we define cast property on our model, Laravel will help us convert defined attributes to common data types. Currently supported cast types (Laravel 5.6) are: integer, real, float, double, string, boolean, object, array,
collection,date, datetime and timestamp.

If those default cast types are not enough and you want to make your own, this Laravel package is here to help you accomplish that.

---

## Compatibility

The package is compatible with Laravel versions 5.5.* and 5.6.*

## Installation

Install the package via composer:

```bash
composer require movor/laravel-custom-casts
```

## Example: Casting User Image

Let's use default Laravel user model found in `app/User.php`.

Beside basic, predefined fields: `name`, `email` and `password`, we also want to allow user to upload his avatar. Assume that we already have users table with `image` field (you should create seeder for this).

To utilize custom casts, we'll need to add trait to user model, and via `$casts` property link it to the cast class.

```php
// File: app/User.php

use App\CustomCasts\ImageCast;

// ...

protected $fillable = [
    'name', 'email', 'password',
    // We need to add "image" as well:
    'image'
];

protected $casts = [
    'image' => ImageCast::class
];

// ...
```

Next step is to create class that'll handle casting. It must implement "setAttribute" method which will take care of saving the image (from UploadedFile object) and generating image name with path - to be preserved in db.

```php
// File: app/CustomCasts/ImageCast.php

namespace App\CustomCasts;

use Movor\LaravelCustomCasts\CustomCastableBase;
use Illuminate\Http\UploadedFile;

class ImageCast extends CustomCastableBase
{
    public function setAttribute($file)
    {
        // Define storage folder
        // (relative to "storage/app" folder in Laravel project)
        // Don't forget to create it !!!
        $storageDir = 'images';

        // Generate random image name
        $filename = str_random() . '.' . $file->extension();

        // Save image to predefined folder
        $file->storeAs($storageDir, $filename);

        // This will be stored in db field: "image"
        return $storageDir . '/' . $filename;
    }
}
```

Let's jump to user creation example. This will trigger our custom cast logic.

Assume that we have user controller which will handle user creation.

```php
// File: app/Http/Controllers/UserController.php

// ...

protected function create(Request $request)
{
    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        // Past the whole Illuminate\Http\UploadedFile object,
        // we'll handle it in our ImageCast class
        'image' => $request->file('image')
    ]);
}

// ...
```

Ok, now we have our user created and image stored.

But we should also handle deleting image when user is deleted. This can be accomplished by utilizing underlying eloquent
events handling. Each time eloquent event is fired, it'll look up for public method with the same name in our custom cast class.

Possible method names are:
`retrieved`, `creating`, `created`, `updating`, `updated`, `saving`, `saved`, `deleting`, `deleted`, `restoring`, `restored`.

```php
// File: app/CustomCasts/ImageCast.php

use Storage;

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