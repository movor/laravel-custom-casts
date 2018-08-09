<?php

namespace Movor\LaravelCustomCasts\Test\Integration;

use Movor\LaravelCustomCasts\Test\Support\Models\Image;
use Movor\LaravelCustomCasts\Test\Support\Models\ImageWithInvalidCustomCast;
use Movor\LaravelCustomCasts\Test\Support\Models\ImageWithMutator;
use Movor\LaravelCustomCasts\Test\Support\Models\ImageWithNoCustomCastsDefined;
use Movor\LaravelCustomCasts\Test\TestCase;

class ModelCanUseCustomCastsTest extends TestCase
{

    public function test_custom_casts_do_not_interfere_with_default_model_casts()
    {
        $imageModel = new Image;
        $imageModel->image = 'data:image/png;image.png';
        $imageModel->data = ['size' => 1000];
        $imageModel->save();

        $imageModel = Image::find($imageModel->id);
        $this->assertTrue(is_array($imageModel->data));

        $imageModel->delete();
    }

    public function test_handle_no_custom_casts_property_defined_with_trait_included()
    {
        $this->expectExceptionMessage('Model class');

        $imageModel = new ImageWithNoCustomCastsDefined;
        $imageModel->image = 'data:image/png;image.png';
        $imageModel->save();

        $imageModel->delete();
    }

    public function test_only_custom_castable_base_children_can_be_in_custom_casts_property()
    {
        $this->expectExceptionMessage('Custom cast class for');

        $imageModel = new ImageWithInvalidCustomCast;
        $imageModel->image = 'data:image/png;image.png';
        $imageModel->save();

        $imageModel->delete();
    }

    public function test_custom_cast_object_can_handle_model_events()
    {
        //
        // Creating
        //

        $imageModel = Image::create([
            // This base64 string is not valid, used just for testing
            'image' => 'data:image/png;image_1.png'
        ]);

        $eventsReceived = self::getEventsReceived($imageModel);

        $this->assertContains('creating', $eventsReceived);
        $this->assertContains('created', $eventsReceived);

        //
        // Updating
        //

        $imageModel = Image::find($imageModel->id);
        $imageModel->image = 'data:image/png;image_2.png';
        $imageModel->save();

        $eventsReceived = self::getEventsReceived($imageModel);

        $this->assertContains('updating', $eventsReceived);
        $this->assertContains('updated', $eventsReceived);

        //
        // Deleting
        //

        $imageModel = Image::find($imageModel->id);
        $imageModel->delete();

        $eventsReceived = self::getEventsReceived($imageModel);

        $this->assertContains('deleting', $eventsReceived);
        $this->assertContains('deleted', $eventsReceived);
    }

    public function test_mutators_has_priority_over_custom_casts()
    {
        $imageName = str_random() . '.png';

        $imageModel = ImageWithMutator::create([
            'image' => $imageName
        ]);

        $imageModel = ImageWithMutator::find($imageModel->id);
        $this->assertEquals($imageName, $imageModel->image);

        $imageModel->delete();
    }

    public function test_can_custom_cast_during_model_creation()
    {
        $imageName = str_random() . '.png';

        $imageModel = Image::create([
            // This base64 string is not valid, used just for testing
            'image' => 'data:image/png;' . $imageName,
        ]);

        $imageModel = Image::find($imageModel->id);
        $this->assertEquals($imageName, $imageModel->image);

        $imageModel->delete();
    }

    public function test_can_custom_cast_during_model_update()
    {
        $imageNameOne = str_random() . '.png';
        $imageNameTwo = str_random() . '.png';

        $imageModel = Image::create([
            'image' => 'data:image/png;' . $imageNameOne
        ]);

        $imageModel->image = 'data:image/png;' . $imageNameTwo;
        $imageModel->save();

        $imageModel = Image::find($imageModel->id);
        $this->assertEquals($imageNameTwo, $imageModel->image);

        $imageModel->delete();
    }

    protected static function getEventsReceived($imageModel)
    {
        $customCastObject = parent::getProtectedProperty($imageModel, 'customCastObjects')['image'];

        return $customCastObject->eventsReceived;
    }
}

