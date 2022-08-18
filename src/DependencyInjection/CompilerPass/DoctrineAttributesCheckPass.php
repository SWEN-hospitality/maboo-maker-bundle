<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineAttributesCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setParameter(
            'bornfight_maboo_maker.compatible_check.doctrine.supports_attributes',
            $container->hasParameter('doctrine.orm.metadata.attribute.class')
        );
    }
}
