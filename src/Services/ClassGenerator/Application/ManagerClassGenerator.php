<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Application;

use Bornfight\MabooMakerBundle\Services\ClassManipulator\EntityField;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class ManagerClassGenerator
{
    public function __construct(private Generator $generator)
    {
    }

    /**
     * @param EntityField[] $fields
     */
    public function generateManagerClass(
        ClassNameDetails $managerClassDetails,
        ClassNameDetails $repositoryInterfaceDetails,
        ClassNameDetails $domainModelClassDetails,
        ClassNameDetails $createWriteModelClassDetails,
        ClassNameDetails $updateWriteModelClassDetails,
        ClassNameDetails $validatorClassDetails,
        array $fields
    ): string {
        return $this->generator->generateClass(
            $managerClassDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/application/Manager.tpl.php',
            [
                'domain_model_full_class_name' => $domainModelClassDetails->getFullName(),
                'domain_model_short_name' => $domainModelClassDetails->getShortName(),
                'repository_interface_full_class_name' => $repositoryInterfaceDetails->getFullName(),
                'repository_interface_short_name' => $repositoryInterfaceDetails->getShortName(),
                'repository_property_name' => Str::asLowerCamelCase($repositoryInterfaceDetails->getShortName()),
                'create_write_model_full_class_name' => $createWriteModelClassDetails->getFullName(),
                'create_write_model_alias' => $createWriteModelClassDetails->getShortName() . 'WriteModel',
                'update_write_model_full_class_name' => $updateWriteModelClassDetails->getFullName(),
                'update_write_model_alias' => $updateWriteModelClassDetails->getShortName() . 'WriteModel',
                'validator_full_class_name' => $validatorClassDetails->getFullName(),
                'validator_short_name' => $validatorClassDetails->getShortName(),
                'validator_property_name' => Str::asLowerCamelCase($validatorClassDetails->getShortName()),
                'object_name' => Str::asLowerCamelCase($domainModelClassDetails->getShortName()),
                'fields_count' => count($fields),
                'fields' => $fields,
            ]
        );
    }
}
