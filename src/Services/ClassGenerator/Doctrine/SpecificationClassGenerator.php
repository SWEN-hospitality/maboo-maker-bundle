<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Doctrine;

use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class SpecificationClassGenerator
{
    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function generateSpecificationClass(
        ClassNameDetails $specificationClassDetails,
        ClassNameDetails $specificationInterfaceDetails,
        ClassNameDetails $repositoryDetails
    ): string {
        return $this->generator->generateClass(
            $specificationClassDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/doctrine/SpecificationClass.tpl.php',
            [
                'specification_interface_full_class_name' => $specificationInterfaceDetails->getFullName(),
                'specification_interface_short_name' => $specificationInterfaceDetails->getShortName(),
                'repository_interface_full_class_name' => $repositoryDetails->getFullName(),
                'repository_interface_short_name' => $repositoryDetails->getShortName(),
                'repository_property_name' => Str::asLowerCamelCase($repositoryDetails->getShortName()),
            ]
        );
    }
}
