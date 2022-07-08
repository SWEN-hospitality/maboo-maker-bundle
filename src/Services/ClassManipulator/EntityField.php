<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassManipulator;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;

class EntityField
{
    public string $name;
    public string $typeHint;
    public bool $isNullable = false;

    public function __construct(string $name, string $typeHint, bool $isNullable = false)
    {
        $this->name = $name;
        $this->typeHint = $typeHint;
        $this->isNullable = $isNullable;
    }

    public static function fromProperty(Property $property): self
    {
        $name = $property->props[0]->name->name;

        if (!($property->type instanceof NullableType)) {
            return new self($name, self::getType($property->type));
        }

        return new self($name, self::getType($property->type->type), true);
    }

    public static function fromParam(Param $param): self
    {
        $name = $param->var->name;

        if (!($param->type instanceof NullableType)) {
            return new self($name, self::getType($param->type));
        }

        return new self($name, self::getType($param->type->type), true);
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

    private static function getType($type)
    {
        if ($type instanceof FullyQualified) {
            return $type->parts[0];
        }

        if ($type instanceof Name) {
            return $type->parts[0];
        }

        if ($type instanceof Identifier) {
            return $type->name;
        }

        return $type->name;
    }
}
