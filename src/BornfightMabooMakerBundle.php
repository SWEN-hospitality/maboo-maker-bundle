<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle;

use Bornfight\MabooMakerBundle\DependencyInjection\CompilerPass\DoctrineAttributesCheckPass;
use Bornfight\MabooMakerBundle\DependencyInjection\CompilerPass\SetDoctrineAnnotatedPrefixesPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BornfightMabooMakerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DoctrineAttributesCheckPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 11);
        $container->addCompilerPass(new SetDoctrineAnnotatedPrefixesPass());
    }
}
