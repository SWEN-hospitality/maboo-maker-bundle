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

    /**
     * @param string|Name|NullableType|Identifier $type
     */
    public function addClassProperty(
        string $name,
        array $annotationLines = [],
        $defaultValue = null,
        array $attributes = [],
        $type = 'string',
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
