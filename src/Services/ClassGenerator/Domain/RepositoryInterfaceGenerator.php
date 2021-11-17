<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Domain;

use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class RepositoryInterfaceGenerator
{
    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function generateRepositoryInterface(
        ClassNameDetails $repositoryInterfaceDetails,
        ClassNameDetails $domainModelClassDetails,
        ClassNameDetails $createWriteModelClassDetails,
        ClassNameDetails $updateWriteModelClassDetails
    ): string {
        return $this->generator->generateClass(
            $repositoryInterfaceDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/domain/RepositoryInterface.tpl.php',
            [
                'domain_model_full_class_name' => $domainModelClassDetails->getFullName(),
                'domain_model_short_name' => $domainModelClassDetails->getShortName(),
                'create_write_model_full_class_name' => $createWriteModelClassDetails->getFullName(),
                'create_write_model_alias' => $createWriteModelClassDetails->getShortName() . 'WriteModel',
                'update_write_model_full_class_name' => $updateWriteModelClassDetails->getFullName(),
                'update_write_model_alias' => $updateWriteModelClassDetails->getShortName() . 'WriteModel',
                'object_name' => Str::asLowerCamelCase($domainModelClassDetails->getShortName()),
            ]
        );
    }
}
