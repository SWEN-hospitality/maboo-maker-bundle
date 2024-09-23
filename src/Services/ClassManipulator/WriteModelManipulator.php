<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassManipulator;

use PhpParser\Builder\Property as PropertyBuilder;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use Symfony\Bundle\MakerBundle\Str;

class WriteModelManipulator extends ClassManipulator
{
    public function addField(string $propertyName, array $columnOptions, array $comments = []): void
    {
        $typeHint = $columnOptions['typeHint'];
        $typeHintShortName = Str::getShortClassName($typeHint);

        $nullable = $columnOptions['nullable'] ?? false;
        $attributes = [];

        $this->addPromotedProperty($propertyName, $typeHintShortName, $nullable, $comments, $attributes, false, true);

        $this->addUseStatementIfNecessary($typeHint);
    }

    public function addForeignKeyField(string $propertyName, array $columnOptions, array $comments = []): void
    {
        $typeHint = 'string';
        $nullable = $columnOptions['nullable'] ?? false;

        $this->addPromotedProperty($propertyName, $typeHint, $nullable, $comments, [], false, true);
    }

    public function addClassProperty(
        string $name,
        array $annotationLines = [],
        $defaultValue = null,
        array $attributes = [],
        string|Name|NullableType|Identifier $type = 'string',
        bool $nullable = false
    ): void {
        if ($this->propertyExists($name)) {
            return;
        }

        $newPropertyNode = $this->buildProperty(
            $name,
            $nullable,
            $type,
            $annotationLines,
            $attributes,
            $defaultValue
        );

        $this->addNodeAfterProperties($newPropertyNode, false);
    }

    protected function createPropertyBuilder(string $name): PropertyBuilder
    {
        $newPropertyBuilder = new PropertyBuilder($name);
        $newPropertyBuilder->makePublic();

        return $newPropertyBuilder;
    }
}
