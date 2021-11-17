<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Doctrine;

use Bornfight\MabooMakerBundle\Services\ClassManipulator\EntityField;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class EntityMapperClassGenerator
{
    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param EntityField[] $fields
     */
    public function generateEntityMapperClass(
        ClassNameDetails $entityMapperClassDetails,
        ClassNameDetails $entityClassDetails,
        ClassNameDetails $domainModelClassDetails,
        array $fields
    ): string {
        return $this->generator->generateClass(
            $entityMapperClassDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/doctrine/EntityMapper.tpl.php',
            [
                'domain_model_full_class_name' => $domainModelClassDetails->getFullName(),
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_alias' => 'Doctrine' . $entityClassDetails->getShortName(),
                'domain_model' => $domainModelClassDetails->getShortName(),
                'fields_count' => count($fields),
                'fields' => array_map(fn (EntityField $field) => ucfirst($field->name), $fields),
            ]
        );
    }
}
