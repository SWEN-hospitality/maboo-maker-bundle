<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassManipulator;

use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Symfony\Bundle\MakerBundle\Str;

class EntityField
{
    public string $name;
    public string $typeHint;
    public bool $isNullable = false;
    /** @var AttributeGroup[]  */
    public array $attrGroups = [];

    public function __construct(
        string $name,
        string $typeHint,
        bool $isNullable = false,
        array $attrGroups = []
    ) {
        $this->name = $name;
        $this->typeHint = $typeHint;
        $this->isNullable = $isNullable;
        $this->attrGroups = $attrGroups;
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
            return new self($name, self::getType($param->type), false, $param->attrGroups);
        }

        return new self($name, self::getType($param->type->type), true,  $param->attrGroups);
    }

    public function isOfPrimitiveType(): bool
    {
        $primitiveTypes = ['int', 'string', 'float', 'bool', 'Date', 'DateTime'];

        return true === in_array($this->typeHint, $primitiveTypes);
    }

    public function isOfAddableType(): bool
    {
        return true;
    }

    public function getOptions(): array
    {
        return [
            'typeHint' => $this->typeHint,
            'nullable' => $this->isNullable,
        ];
    }

    public function isManyToOneField(): bool
    {
        foreach ($this->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {

                $nameParts = $attribute->name->parts;
                if (sizeof($nameParts) < 2) {
                    continue;
                }

                if ($nameParts[0] === 'ORM' && $nameParts[1] === 'ManyToOne') {
                    return true;
                }
            }
        }
        return false;
    }

    public function foreignKeyName(): string
    {
        return Str::addSuffix($this->name, 'Id');
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
