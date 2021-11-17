<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Util;

use ReflectionClass;
use ReflectionProperty;

trait ClassProperties
{
    public function getPropertyNames(string $class): array
    {
        if (!class_exists($class)) {
            return [];
        }

        $reflectionClass = new ReflectionClass($class);

        return array_map(function (ReflectionProperty $prop) {
            return $prop->getName();
        }, $reflectionClass->getProperties());
    }

    public function getPropertyData(string $class): array
    {
        if (!class_exists($class)) {
            return [];
        }

        $reflectionClass = new ReflectionClass($class);

        return array_map(function (ReflectionProperty $prop) {
            return $prop->getName();
        }, $reflectionClass->getProperties());
    }
}
