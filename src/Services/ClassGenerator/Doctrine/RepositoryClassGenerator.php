<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Doctrine;

use Bornfight\MabooMakerBundle\Services\ClassManipulator\EntityField;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class RepositoryClassGenerator
{
    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param EntityField[] $fields
     */
    public function generateRepositoryClass(
        ClassNameDetails $repositoryClassDetails,
        ClassNameDetails $repositoryInterfaceDetails,
        ClassNameDetails $domainModelClassDetails,
        ClassNameDetails $createWriteModelClassDetails,
        ClassNameDetails $updateWriteModelClassDetails,
        ClassNameDetails $entityClassDetails,
        ClassNameDetails $entityMapperClassDetails,
        array $fields
    ): string {
        return $this->generator->generateClass(
            $repositoryClassDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/doctrine/RepositoryClass.tpl.php',
            [
                'domain_model_full_class_name' => $domainModelClassDetails->getFullName(),
                'domain_model_short_name' => $domainModelClassDetails->getShortName(),
                'repository_interface_full_class_name' => $repositoryInterfaceDetails->getFullName(),
                'repository_interface_short_name' => $repositoryInterfaceDetails->getShortName(),
                'create_write_model_full_class_name' => $createWriteModelClassDetails->getFullName(),
                'create_write_model_alias' => $createWriteModelClassDetails->getShortName() . 'WriteModel',
                'update_write_model_full_class_name' => $updateWriteModelClassDetails->getFullName(),
                'update_write_model_alias' => $updateWriteModelClassDetails->getShortName() . 'WriteModel',
                'object_name' => Str::asLowerCamelCase($domainModelClassDetails->getShortName()),
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_alias' => 'Doctrine' . $entityClassDetails->getShortName(),
                'entity_mapper_full_class_name' => $entityMapperClassDetails->getFullName(),
                'entity_mapper_short_name' => $entityMapperClassDetails->getShortName(),
                'db_table_alias' => $this->getEntityNameInitials($entityClassDetails->getShortName()),
                'fields_count' => count($fields),
                'fields' => array_map(
                    fn (EntityField $field) => $field->getDomainFieldName(),
                    $fields
                ),
                'foreignKeys' => array_combine(
                    array_map(
                        fn (EntityField $field) => $field->getDomainFieldName(),
                        array_filter(
                            $fields,
                            fn (EntityField $field) => $field->isManyToOneField()
                        )
                    ),
                    array_map(
                        fn (EntityField $field) => [
                            'nullable' => $field->isNullable,
                            'name' => $field->name,
                            'entityAlias' => 'Doctrine' . $field->typeHint,
                            'domainFieldName' => $field->getDomainFieldName(),
                        ],
                        array_filter(
                            $fields,
                            fn (EntityField $field) => $field->isManyToOneField()
                        )
                    )
                ),
                'field_setters' => array_map(fn (EntityField $field) => 'set' . Str::asCamelCase($field->name), $fields)
            ]
        );
    }

    private function getEntityNameInitials(string $entity): string
    {
        $nameInSnakeCase = Str::asSnakeCase($entity);

        $entityNameWords = explode('_', $nameInSnakeCase);

        return join('', array_map(fn (string $word) => $word[0], $entityNameWords));
    }
}
