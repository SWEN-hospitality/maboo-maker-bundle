<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Util;

use Bornfight\MabooMakerBundle\Services\ClassManipulator\EntityField;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Bundle\MakerBundle\Util\ClassDetails;

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

    public function getPathOfClass(string $class): string
    {
        $classDetails = new ClassDetails($class);

        return $classDetails->getPath();
    }

    /**
     * EntityField[] $currentDomainModelFields
     */
    public function isFieldAlreadyInClass(array $currentClassFields, EntityField $entityField): bool
    {
        return in_array(
            $entityField->name,
            array_map(
                fn (EntityField $field) => $field->name,
                $currentClassFields
            )
        );
    }
}
