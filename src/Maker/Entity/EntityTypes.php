<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\Entity;

use Doctrine\DBAL\Types\Type;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Doctrine\EntityRelation;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;

class EntityTypes
{
    public function getAllTypes(): array
    {
        return Type::getTypesMap();
    }

    public function getTypesTable(): array
    {
        return [
            'main' => [
                'string' => [],
                'text' => [],
                'boolean' => [],
                'integer' => ['smallint', 'bigint'],
                'float' => [],
                'ap_decimal' => ['decimal']
            ],
            'relation' => [
                'relation' => 'a wizard will help you build the relation',
                EntityRelation::MANY_TO_ONE => [],
                EntityRelation::ONE_TO_MANY => [],
                EntityRelation::MANY_TO_MANY => [],
                EntityRelation::ONE_TO_ONE => [],
            ],
            'array_object' => [
                'array' => ['simple_array'],
                'json' => [],
                'object' => [],
                'binary' => [],
                'blob' => [],
            ],
            'date_time' => [
                'datetime' => ['datetime_immutable'],
                'datetimetz' => ['datetimetz_immutable'],
                'date' => ['date_immutable'],
                'time' => ['time_immutable'],
                'dateinterval' => [],
            ],
        ];
    }

    public function determineDefaultTypeBasedOnFieldName(string $fieldName): string
    {
        // try to guess the type by the field name prefix/suffix
        // convert to snake case for simplicity
        $snakeCasedField = Str::asSnakeCase($fieldName);

        if ('_at' === $suffix = substr($snakeCasedField, -3)) {
            return 'datetime';
        }

        if ('_id' === $suffix) {
            return 'integer';
        }

        if (0 === strpos($snakeCasedField, 'is_') || 0 === strpos($snakeCasedField, 'has_')) {
            return 'boolean';
        }

        if ('uuid' === $snakeCasedField) {
            return 'uuid';
        }

        if ('guid' === $snakeCasedField) {
            return 'guid';
        }

        return 'string';
    }

    public function getAllValidTypes(): array
    {
        $types = $this->getAllTypes();

        return array_merge(
            array_keys($types),
            $this->getAllValidRelationTypes(),
            ['relation']
        );
    }

    public function getAllValidRelationTypes(): array
    {
        return EntityRelation::getValidRelationTypes();
    }

    public function getFieldData(ConsoleStyle $io, string $fieldName, string $type): array
    {
        // this is a normal field
        $data = ['fieldName' => $fieldName, 'type' => $type];
        if ('string' === $type) {
            // default to 255, avoid the question
            $data['length'] = (int) $io->ask(
                'Field length',
                '255',
                [Validator::class, 'validateLength']
            );
        } elseif ('decimal' === $type || 'ap_decimal' === $type) {
            // 10 is the default value given in \Doctrine\DBAL\Schema\Column::$_precision
            $data['precision'] = (int) $io->ask(
                'Precision (total number of digits stored: 100.00 would be 5)',
                '10',
                [Validator::class, 'validatePrecision']
            );

            // 0 is the default value given in \Doctrine\DBAL\Schema\Column::$_scale
            $data['scale'] = (int) $io->ask(
                'Scale (number of decimals to store: 100.00 would be 2)',
                '2',
                [Validator::class, 'validateScale']
            );
        }

        if ($io->confirm('Can this field be null in the database (nullable)', false)) {
            $data['nullable'] = true;
        }

        return $data;
    }

    public function typeIsRelation(string $type): bool
    {
        return 'relation' === $type || in_array($type, EntityRelation::getValidRelationTypes());
    }
}
