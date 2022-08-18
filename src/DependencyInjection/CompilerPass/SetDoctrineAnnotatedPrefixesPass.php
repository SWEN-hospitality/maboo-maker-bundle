<?php

namespace Bornfight\MabooMakerBundle\DependencyInjection\CompilerPass;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver as AbstractAnnotationDriver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SetDoctrineAnnotatedPrefixesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $annotatedPrefixes = null;

        foreach ($container->findTaggedServiceIds('doctrine.orm.configuration') as $id => $tags) {
            $metadataDriverImpl = null;
            foreach ($container->getDefinition($id)->getMethodCalls() as [$method, $arguments]) {
                if ('setMetadataDriverImpl' === $method) {
                    $metadataDriverImpl = $container->getDefinition($arguments[0]);
                    break;
                }
            }

            if (null === $metadataDriverImpl || !preg_match('/^doctrine\.orm\.(.+)_configuration$/D', $id, $m)) {
                continue;
            }

            $managerName = $m[1];
            $methodCalls = $metadataDriverImpl->getMethodCalls();

            foreach ($methodCalls as $i => [$method, $arguments]) {
                if ('addDriver' !== $method) {
                    continue;
                }

                if ($arguments[0] instanceof Definition) {
                    $class = $arguments[0]->getClass();
                    $namespace = substr($class, 0, strrpos($class, '\\'));

                    $id = sprintf('.%d_doctrine_metadata_driver~%s', $i, ContainerBuilder::hash($arguments));
                    $container->setDefinition($id, $arguments[0]);
                    $arguments[0] = new Reference($id);
                    $methodCalls[$i] = [$method, $arguments];
                }

                $annotatedPrefixes[$managerName][] = [
                    $arguments[1],
                    new Reference($arguments[0]),
                ];
            }

            $metadataDriverImpl->setMethodCalls($methodCalls);
        }

        if (null !== $annotatedPrefixes) {
            $container->getDefinition('bornfight_maboo_maker.doctrine_helper')->setArgument(3, $annotatedPrefixes);
        }
    }
}
