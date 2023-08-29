<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassManipulator;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use Symfony\Bundle\MakerBundle\Str;

class EntityManipulator extends ClassManipulator
{
    public function __construct(string $sourceCode, bool $useAnnotations, bool $useAttributes, bool $fluentMutators)
    {
        $overwrite = false;

        parent::__construct($sourceCode, $overwrite, $useAnnotations, $fluentMutators, $useAttributes);
    }

    public function addField(string $propertyName, array $columnOptions, array $comments = []): void
    {
        $typeHint = $this->getTypeHint($columnOptions['type']);
        $columnType = $this->getColumnType($columnOptions['type']);
        $typeHintShortName = Str::getShortClassName($typeHint);
        $nullable = $columnOptions['nullable'] ?? false;
        $isId = (bool) ($columnOptions['id'] ?? false);
        $attributes = [];

        if (true === $this->useAttributesForDoctrineMapping) {
            if (null !== $columnType) {
                $columnOptions['type'] = $columnType;
            }
            $attributes[] = $this->buildAttributeNode(Column::class, $columnOptions, 'ORM');
        } else {
            $comments[] = $this->buildAnnotationLine('@ORM\Column', $columnOptions);
        }

        $this->addClassFieldAsPromotedProperty($propertyName, $typeHintShortName, $nullable, $comments, $attributes);
        $this->addUseStatementIfNecessary($typeHint);
        if (null !== $columnType) {
            if (str_starts_with($columnType, 'DecimalType')) {
                $this->addUseStatementIfNecessary('App\\Shared\\Infrastructure\\Persistence\\Doctrine\\Types\\ArbitraryPrecisionDecimalType');
            }
            if (str_starts_with($columnType, 'DateRangeType')) {
                $this->addUseStatementIfNecessary('App\\Shared\\Infrastructure\\Persistence\\Doctrine\\Types\\DateRangeType');
            }
            if (str_starts_with($columnType, 'Doctrine\\DBAL\\Types\\Type')) {
                $this->addUseStatementIfNecessary('Doctrine\\DBAL\\Types\\Types');
            }
        }
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
        return match ($doctrineType) {
            'string', 'text', 'guid', 'bigint' => 'string',
            'array', 'simple_array', 'json', 'json_array' => 'array',
            'boolean' => 'bool',
            'decimal', 'ap_decimal' => 'Decimal\\Decimal',
            'integer', 'smallint' => 'int',
            'float' => 'float',
            'datetime', 'datetimetz', 'date', 'time' => DateTime::class,
            'datetime_immutable', 'datetimetz_immutable', 'date_immutable', 'time_immutable' => DateTimeImmutable::class,
            'dateinterval' => DateInterval::class,
            'date_range' => 'App\\Shared\\Domain\\ValueObject\\DateTimeRange',
            default => null,
        };
    }

    protected function getColumnType(string $doctrineType): ?string
    {
        return match ($doctrineType) {
            'text' => 'Doctrine\\DBAL\\Types\\Types::TEXT',
            'decimal', 'ap_decimal' => 'DecimalType::NAME',
            'datetime', 'datetimetz' => 'Doctrine\\DBAL\\Types\\Types::DATETIME_MUTABLE',
            'date' => 'Doctrine\\DBAL\\Types\\Types::DATE_MUTABLE',
            'time' => 'Doctrine\\DBAL\\Types\\Types:TIME_MUTABLE',
            'date_range' => 'DateRangeType::NAME',
            default => null,
        };
    }
}
