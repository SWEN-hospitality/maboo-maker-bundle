<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Infrastructure;

use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class ResolverClassGenerator
{
    private Generator $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function generateResolverClass(
        ClassNameDetails $resolverClassDetails,
        ClassNameDetails $repositoryInterfaceDetails,
        ClassNameDetails $domainModelClassDetails,
        string $entityVarSingular,
        string $entityVarPlural
    ): string {

        return $this->generator->generateClass(
            $resolverClassDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/infrastructure/Resolver.tpl.php',
            [
                'domain_model_full_class_name' => $domainModelClassDetails->getFullName(),
                'domain_model_short_name' => $domainModelClassDetails->getShortName(),
                'repository_interface_full_class_name' => $repositoryInterfaceDetails->getFullName(),
                'repository_interface_short_name' => $repositoryInterfaceDetails->getShortName(),
                'repository_property_name' => Str::asLowerCamelCase($repositoryInterfaceDetails->getShortName()),
                'resource_name' => $entityVarSingular,
                'collection_name' => $entityVarPlural,
            ]
        );
    }
}
