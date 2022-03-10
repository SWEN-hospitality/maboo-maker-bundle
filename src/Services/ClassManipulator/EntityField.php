<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassManipulator;

use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Property;

class EntityField
{
    public string $name;
    public bool $isNullable = false;
    public string $typeHint;

    public function __construct(Property $property)
    {
        $this->name = $property->props[0]->name->name;

        if (!($property->type instanceof NullableType)) {
            if ($property->type instanceof FullyQualified) {
                $this->typeHint = $property->type->parts[0];

                return;
            }

            if ($property->type instanceof Name) {
                $this->typeHint = $property->type->parts[0];

                return;
            }

            $this->typeHint = $property->type->name;

            return;
        }

        $this->isNullable = true;

        if ($property->type->type instanceof FullyQualified) {
            $this->typeHint = $property->type->type->parts[0];

            return;
        }

        if ($property->type->type instanceof Name) {
            $this->typeHint = $property->type->type->parts[0];

            return;
        }

        $this->typeHint = $property->type->type->name;
    }

    public function isOfPrimitiveType(): bool
    {
        $primitiveTypes = ['int', 'string', 'float', 'bool', 'Date', 'DateTime'];

        return true === in_array($this->typeHint, $primitiveTypes);
    }

    public function isOfAddableType(): bool
    {
        return $this->isOfPrimitiveType();
    }

    public function getOptions(): array
    {
        return [
            'typeHint' => $this->typeHint,
            'nullable' => $this->isNullable,
        ];
    }
}
