<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function getPropertyValueFromObject(object $object, string $propertyName): mixed
    {
        $reflectionClass = new \ReflectionClass($object);
        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
