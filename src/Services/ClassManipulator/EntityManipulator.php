<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassManipulator;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;

class EntityManipulator extends ClassManipulator
{
    public function __construct(string $sourceCode, bool $useAnnotations, bool $fluentMutators)
    {
        $overwrite = false;
        $useAttributes = false;

        parent::__construct($sourceCode, $overwrite, $useAnnotations, $fluentMutators, $useAttributes);
    }

    public function addField(string $propertyName, array $columnOptions, array $comments = []): void
    {
        $typeHint = $this->getTypeHint($columnOptions['type']);
        $nullable = $columnOptions['nullable'] ?? false;
        $isId = (bool) ($columnOptions['id'] ?? false);

        $comments[] = $this->buildAnnotationLine('@ORM\Column', $columnOptions);

        $this->addClassFieldAsPromotedProperty($propertyName, $typeHint, $nullable, $comments);
        $this->addGetter($propertyName,$typeHint, $nullable);

        // don't generate setters for id fields
        if (!$isId) {
            $this->addEntitySetter($propertyName, $typeHint, $nullable);
        }
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

        $this->addNodeAfterProperties($newPropertyNode);
    }

    protected function getTypeHint(string $doctrineType): ?string
    {
        switch ($doctrineType) {
            case 'string':
            case 'text':
            case 'guid':
            case 'bigint':
            case 'decimal':
                return 'string';

            case 'array':
            case 'simple_array':
            case 'json':
            case 'json_array':
                return 'array';

            case 'boolean':
                return 'bool';

            case 'integer':
            case 'smallint':
                return 'int';

            case 'float':
                return 'float';

            case 'datetime':
            case 'datetimetz':
            case 'date':
            case 'time':
                return '\\' . DateTime::class;

            case 'datetime_immutable':
            case 'datetimetz_immutable':
            case 'date_immutable':
            case 'time_immutable':
                return '\\' . DateTimeImmutable::class;

            case 'dateinterval':
                return '\\' . DateInterval::class;

            case 'object':
            case 'binary':
            case 'blob':
            default:
                return null;
        }
    }
}
