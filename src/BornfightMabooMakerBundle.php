<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle;

use Bornfight\MabooMakerBundle\DependencyInjection\CompilerPass\SetDoctrineAnnotatedPrefixesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BornfightMabooMakerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SetDoctrineAnnotatedPrefixesPass());
    }
}
