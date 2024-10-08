<?php

namespace Bornfight\MabooMakerBundle\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\MappingException as ORMMappingException;
use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Persistence\Mapping\MappingException as PersistenceMappingException;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Ryan Weaver <ryan@knpuniversity.com>
 * @author Sadicov Vladimir <sadikoff@gmail.com>
 * @author Goran Hrženjak <goran.hrzenjak@bornfight.com>
 */
class DoctrineHelper
{
    private string $entityNamespace;

    private ManagerRegistry $registry;

    private ?array $annotatedPrefixes;

    private bool $attributeMappingSupport;

    /**
     * @var ManagerRegistry
     */
    public function __construct(
        string $entityNamespace,
        $registry = null,
        bool $attributeMappingSupport = false,
        array $annotatedPrefixes = null
    ) {
        $this->entityNamespace = trim($entityNamespace, '\\');
        $this->registry = $registry;
        $this->attributeMappingSupport = $attributeMappingSupport;
        $this->annotatedPrefixes = $annotatedPrefixes;
    }

    /**
     * @return ManagerRegistry
     */
    public function getRegistry(): ?ManagerRegistry
    {
        // this should never happen: we will have checked for the
        // DoctrineBundle dependency before calling this
        if (null === $this->registry) {
            throw new \Exception('Somehow the doctrine service is missing. Is DoctrineBundle installed?');
        }

        return $this->registry;
    }

    private function isDoctrineInstalled(): bool
    {
        return null !== $this->registry;
    }

    public function getEntityNamespace(): string
    {
        return $this->entityNamespace;
    }

    public function isClassAnnotated(string $className): bool
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getRegistry()->getManagerForClass($className);

        if (null === $em) {
            throw new \InvalidArgumentException(sprintf('Cannot find the entity manager for class "%s"', $className));
        }

        if (null === $this->annotatedPrefixes) {
            // doctrine-bundle <= 2.2
            $metadataDriver = $em->getConfiguration()->getMetadataDriverImpl();

            if (!$this->isInstanceOf($metadataDriver, MappingDriverChain::class)) {
                return $metadataDriver instanceof AnnotationDriver;
            }

            foreach ($metadataDriver->getDrivers() as $namespace => $driver) {
                if (0 === strpos($className, $namespace)) {
                    return $driver instanceof AnnotationDriver;
                }
            }

            return $metadataDriver->getDefaultDriver() instanceof AnnotationDriver;
        }

        $managerName = array_search($em, $this->getRegistry()->getManagers(), true);

        foreach ($this->annotatedPrefixes[$managerName] as [$prefix, $annotationDriver]) {
            if (0 === strpos($className, $prefix)) {
                return null !== $annotationDriver;
            }
        }

        return false;
    }

    public function doesClassUsesAttributes(string $className): bool
    {
        try {
            /** @var EntityManagerInterface $em */
            $em = $this->getRegistry()->getManagerForClass($className);
        } catch (\ReflectionException $exception) {
            // this exception will be thrown by the registry if the class isn't created yet.
            // an example case is the "make:entity" command, which needs to know which driver is used for the class to determine
            // if the class should be generated with attributes or annotations. If this exception is thrown, we will check based on the
            // namespaces for the given $className and compare it with the doctrine configuration to get the correct MappingDriver.

            return $this->isInstanceOf($this->getMappingDriverForNamespace($className), AttributeDriver::class);
        }

        if (null === $em) {
            throw new \InvalidArgumentException(sprintf('Cannot find the entity manager for class "%s"', $className));
        }

        if (null === $this->annotatedPrefixes) {
            // doctrine-bundle <= 2.2
            $metadataDriver = $em->getConfiguration()->getMetadataDriverImpl();

            if (!$this->isInstanceOf($metadataDriver, MappingDriverChain::class)) {
                return $this->isInstanceOf($metadataDriver, AttributeDriver::class);
            }

            foreach ($metadataDriver->getDrivers() as $namespace => $driver) {
                if (0 === strpos($className, $namespace)) {
                    return $this->isInstanceOf($driver, AttributeDriver::class);
                }
            }

            return $this->isInstanceOf($metadataDriver->getDefaultDriver(), AttributeDriver::class);
        }

        $managerName = array_search($em, $this->getRegistry()->getManagers(), true);

        foreach ($this->annotatedPrefixes[$managerName] as [$prefix, $annotationDriver]) {
            if (0 === strpos($className, $prefix)) {
                return $this->isInstanceOf($annotationDriver, AttributeDriver::class);
            }
        }

        return false;
    }

    public function isDoctrineSupportingAttributes(): bool
    {
        return $this->isDoctrineInstalled() && true === $this->attributeMappingSupport;
    }

    public function getEntitiesForAutocomplete(): array
    {
        $entities = [];

        if ($this->isDoctrineInstalled()) {
            $allMetadata = $this->getMetadata();

            /* @var ClassMetadata $metadata */
            foreach (array_keys($allMetadata) as $classname) {
                $entityClassDetails = new ClassNameDetails($classname, $this->entityNamespace);
                $entities[] = $entityClassDetails->getRelativeName();
            }
        }

        sort($entities);

        return $entities;
    }

    /**
     * @return array|ClassMetadata
     */
    public function getMetadata(string $classOrNamespace = null, bool $disconnected = false)
    {
        $classNames = (new \ReflectionClass(AnnotationDriver::class))->getProperty('classNames');
        $classNames->setAccessible(true);

        // Invalidating the cached AnnotationDriver::$classNames to find new Entity classes
        foreach ($this->annotatedPrefixes ?? [] as $managerName => $prefixes) {
            foreach ($prefixes as [$prefix, $annotationDriver]) {
                if (null !== $annotationDriver) {
                    $classNames->setValue($annotationDriver, null);
                }
            }
        }

        $metadata = [];

        /** @var EntityManagerInterface $em */
        foreach ($this->getRegistry()->getManagers() as $em) {
            $cmf = $em->getMetadataFactory();

            if ($disconnected) {
                try {
                    $loaded = $cmf->getAllMetadata();
                } catch (ORMMappingException $e) {
                    $loaded = $this->isInstanceOf($cmf, AbstractClassMetadataFactory::class) ? $cmf->getLoadedMetadata() : [];
                } catch (PersistenceMappingException $e) {
                    $loaded = $this->isInstanceOf($cmf, AbstractClassMetadataFactory::class) ? $cmf->getLoadedMetadata() : [];
                }

                $cmf = new DisconnectedClassMetadataFactory();
                $cmf->setEntityManager($em);

                foreach ($loaded as $m) {
                    $cmf->setMetadataFor($m->getName(), $m);
                }

                if (null === $this->annotatedPrefixes) {
                    // Invalidating the cached AnnotationDriver::$classNames to find new Entity classes
                    $metadataDriver = $em->getConfiguration()->getMetadataDriverImpl();
                    if ($this->isInstanceOf($metadataDriver, MappingDriverChain::class)) {
                        foreach ($metadataDriver->getDrivers() as $driver) {
                            if ($this->isInstanceOf($driver, AnnotationDriver::class)) {
                                $classNames->setValue($driver, null);
                            }
                        }
                    }
                }
            }

            foreach ($cmf->getAllMetadata() as $m) {
                if (null === $classOrNamespace) {
                    $metadata[$m->getName()] = $m;
                } else {
                    if ($m->getName() === $classOrNamespace) {
                        return $m;
                    }

                    if (0 === strpos($m->getName(), $classOrNamespace)) {
                        $metadata[$m->getName()] = $m;
                    }
                }
            }
        }

        return $metadata;
    }

    /**
     * @return EntityDetails|null
     */
    public function createDoctrineDetails(string $entityClassName)
    {
        $metadata = $this->getMetadata($entityClassName);

        if ($this->isInstanceOf($metadata, ClassMetadata::class)) {
            return new EntityDetails($metadata);
        }

        return null;
    }

    public function isClassAMappedEntity(string $className): bool
    {
        if (!$this->isDoctrineInstalled()) {
            return false;
        }

        return (bool) $this->getMetadata($className);
    }

    private function isInstanceOf($object, string $class): bool
    {
        if (!\is_object($object)) {
            return false;
        }

        $legacyClass = str_replace('Doctrine\\Persistence\\', 'Doctrine\\Common\\Persistence\\', $class);

        return $object instanceof $class || $object instanceof $legacyClass;
    }

    public function getPotentialTableName(string $className): string
    {
        $entityManager = $this->getRegistry()->getManager();

        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \RuntimeException('ObjectManager is not an EntityManagerInterface.');
        }

        /** @var NamingStrategy $namingStrategy */
        $namingStrategy = $entityManager->getConfiguration()->getNamingStrategy();

        return $namingStrategy->classToTableName($className);
    }

    public function isKeyword(string $name): bool
    {
        /** @var Connection $connection */
        $connection = $this->getRegistry()->getConnection();

        return $connection->getDatabasePlatform()->getReservedKeywordsList()->isKeyword($name);
    }

    /**
     * this method tries to find the correct MappingDriver for the given namespace/class
     * To determine which MappingDriver belongs to the class we check the prefixes configured in Doctrine and use the
     * prefix that has the closest match to the given $namespace.
     *
     * this helper function is needed to create entities with the configuration of doctrine if they are not yet been registered
     * in the ManagerRegistry
     */
    private function getMappingDriverForNamespace(string $namespace): ?MappingDriver
    {
        $lowestCharacterDiff = null;
        $foundDriver = null;

        foreach ($this->annotatedPrefixes ?? [] as $mappings) {
            foreach ($mappings as [$prefix, $driver]) {
                $diff = substr_compare($namespace, $prefix, 0);

                if ($diff >= 0 && (null === $lowestCharacterDiff || $diff < $lowestCharacterDiff)) {
                    $lowestCharacterDiff = $diff;
                    $foundDriver = $driver;
                }
            }
        }

        return $foundDriver;
    }
}
