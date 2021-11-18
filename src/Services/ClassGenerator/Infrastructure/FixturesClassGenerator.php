<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Infrastructure;

use Bornfight\MabooMakerBundle\Services\ClassManipulator\EntityField;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class FixturesClassGenerator
{
    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param EntityField[] $fields
     */
    public function generateFixturesClass(
        ClassNameDetails $fixturesClassDetails,
        ClassNameDetails $entityClassDetails,
        ClassNameDetails $domainModelClassDetails,
        array $fields
    ): string {

        $fieldNames = [];
        $fieldFixtureValues = [];

        foreach ($fields as $field) {
            if ('id' === $field->name) {
                continue;
            }

            $fieldNames[] = $field->name;

            switch (true) {
                case 'int' === $field->typeHint:
                    $fieldFixtureValues[] = '10';
                    break;
                case 'string' === $field->typeHint:
                    $fieldFixtureValues[] = '\'example\'';
                    break;
                case 'float' === $field->typeHint:
                    $fieldFixtureValues[] = '20.50';
                    break;
                case 'bool' === $field->typeHint:
                    $fieldFixtureValues[] = 'true';
                    break;
                default:
                    $fieldFixtureValues[] = '\'Undefined\'';
            }
        }

        return $this->generator->generateClass(
            $fixturesClassDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/infrastructure/Fixtures.tpl.php',
            [
                'domain_model' => $domainModelClassDetails->getShortName(),
                'object_name' => Str::asLowerCamelCase($domainModelClassDetails->getShortName()),
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_alias' => 'Doctrine' . $entityClassDetails->getShortName(),
                'fields_count' => count($fields),
                'field_names' => $fieldNames,
                'field_fixture_values' => $fieldFixtureValues,
            ]
        );
    }
}
