<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Infrastructure;

use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class MutationClassGenerator
{
    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function generateMutationClass(
        ClassNameDetails $mutationClassDetails,
        ClassNameDetails $managerClassDetails,
        ClassNameDetails $domainModelClassDetails
    ): string {

        return $this->generator->generateClass(
            $mutationClassDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/infrastructure/Mutation.tpl.php',
            [
                'domain_model' => $domainModelClassDetails->getShortName(),
                'manager_full_class_name' => $managerClassDetails->getFullName(),
                'manager_short_name' => $managerClassDetails->getShortName(),
                'manager_property_name' => Str::asLowerCamelCase($managerClassDetails->getShortName()),
                'object_name' => Str::asLowerCamelCase($domainModelClassDetails->getShortName()),
            ]
        );
    }
}
