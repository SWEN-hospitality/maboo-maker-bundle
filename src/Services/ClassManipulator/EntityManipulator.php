<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassManipulator;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use Symfony\Bundle\MakerBundle\Str;

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
        $typeHintShortName = Str::getShortClassName($typeHint);
        $nullable = $columnOptions['nullable'] ?? false;
        $isId = (bool) ($columnOptions['id'] ?? false);

        $comments[] = $this->buildAnnotationLine('@ORM\Column', $columnOptions);

        $this->addClassFieldAsPromotedProperty($propertyName, $typeHintShortName, $nullable, $comments);
        $this->addUseStatementIfNecessary($typeHint);
        $this->addGetter($propertyName, $typeHintShortName, $nullable);

        // don't generate setters for id fields
        if (!$isId) {
            $this->addEntitySetter($propertyName, $typeHintShortName, $nullable);
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
                return 'string';

            case 'array':
            case 'simple_array':
            case 'json':
            case 'json_array':
                return 'array';

            case 'boolean':
                return 'bool';

            case 'decimal':
            case 'ap_decimal':
                return 'Decimal\\Decimal';

            case 'integer':
            case 'smallint':
                return 'int';

            case 'float':
                return 'float';

            case 'datetime':
            case 'datetimetz':
            case 'date':
            case 'time':
                return DateTime::class;

            case 'datetime_immutable':
            case 'datetimetz_immutable':
            case 'date_immutable':
            case 'time_immutable':
                return DateTimeImmutable::class;

            case 'dateinterval':
                return DateInterval::class;

            case 'date_range':
                return 'App\\Shared\\Domain\\ValueObject\\DateTimeRange';

            case 'object':
            case 'binary':
            case 'blob':
            default:
                return null;
        }
    }
}
