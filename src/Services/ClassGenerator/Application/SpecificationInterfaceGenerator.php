<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Application;

use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class SpecificationInterfaceGenerator
{
    public function __construct(private Generator $generator)
    {
    }

    public function generateSpecificationInterface(ClassNameDetails $specificationInterfaceDetails): string
    {
        return $this->generator->generateClass(
            $specificationInterfaceDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/application/SpecificationInterface.tpl.php',
            []
        );
    }
}
