<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassManipulator;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use Symfony\Bundle\MakerBundle\Str;

class DomainModelManipulator extends ClassManipulator
{
    public function __construct(string $sourceCode, bool $useAnnotations)
    {
        $overwrite = false;
        $useAttributes = false;
        $fluentMutators = false;

        parent::__construct($sourceCode, $overwrite, $useAnnotations, $fluentMutators, $useAttributes);
    }

    public function addField(string $propertyName, array $columnOptions, array $comments = []): void
    {
        $typeHint = $columnOptions['typeHint'];
        $typeHintShortName = Str::getShortClassName($typeHint);
        $nullable = $columnOptions['nullable'] ?? false;
        $attributes = [];

        $this->addPromotedProperty($propertyName, $typeHintShortName, $nullable, $comments, $attributes);

        $this->addUseStatementIfNecessary($typeHint);

        $this->addGetter($propertyName,$typeHintShortName, $nullable);
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
}
