<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\ClassGenerator\Doctrine\EntityMapperClassGenerator;
use Bornfight\MabooMakerBundle\Services\ClassManipulator\ClassManipulatorManager;
use Bornfight\MabooMakerBundle\Services\Interactor;
use Bornfight\MabooMakerBundle\Services\NamespaceService;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class MakeEntityMapper extends PlainMaker
{
    private EntityMapperClassGenerator $entityMapperClassGenerator;
    private NamespaceService $namespaceService;
    private ClassManipulatorManager $manipulatorManager;

    public function __construct(
        Interactor $interactor,
        EntityMapperClassGenerator $entityMapperClassGenerator,
        NamespaceService $namespaceService,
        ClassManipulatorManager $manipulatorManager
    ) {
        parent::__construct($interactor);

        $this->entityMapperClassGenerator = $entityMapperClassGenerator;
        $this->namespaceService = $namespaceService;
        $this->manipulatorManager = $manipulatorManager;
    }

    public static function getCommandName(): string
    {
        return 'make:maboo-entity-mapper';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates or updates mapper for a model class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $this->buildCommand($command)
            ->addModuleArgumentToCommand($command, $inputConfig)
            ->addEntityArgumentToCommand($command, $inputConfig)
            ->addDomainModelArgumentToCommand($command, $inputConfig)
            ->addEntityMapperArgumentToCommand($command, $inputConfig);
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $this->interactor->collectEntityMapperArguments($input, $io, $command);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $module = $input->getArgument($this->interactor->getModuleArg());
        $entity = $input->getArgument($this->interactor->getEntityArg());
        $model = $input->getArgument($this->interactor->getDomainModelArg());
        $entityMapper = $input->getArgument($this->interactor->getEntityMapperArg());

        $entityMapperClassDetails = $generator->createClassNameDetails(
            $entityMapper,
            $this->namespaceService->getEntityMapperNamespace()
        );

        $entityClassDetails = $generator->createClassNameDetails(
            $entity,
            $this->namespaceService->getEntityNamespace()
        );

        $domainModelClassDetails = $generator->createClassNameDetails(
            $model,
            $this->namespaceService->getDomainModelNamespace($module)
        );

        $mapperClassExists = class_exists($entityMapperClassDetails->getFullName());
        if (true === $mapperClassExists) {
            throw new RuntimeCommandException('Updating existing mappers is not yet supported!');
        }

        if (false === class_exists($entityClassDetails->getFullName())) {
            throw new RuntimeCommandException('Entity class does not exits!');
        }

        $currentEntityFields = $this->manipulatorManager->getEntityFields($entityClassDetails->getFullName());

        if (false === $mapperClassExists) {
            $this->entityMapperClassGenerator->generateEntityMapperClass(
                $entityMapperClassDetails,
                $entityClassDetails,
                $domainModelClassDetails,
                $currentEntityFields
            );

            $generator->writeChanges();

            $this->echoSuccessMessages('Entity mapper generated and updated!', $io);
        }
    }

}
