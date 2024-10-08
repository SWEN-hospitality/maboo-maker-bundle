<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\ClassGenerator\Infrastructure\FixturesClassGenerator;
use Bornfight\MabooMakerBundle\Services\ClassManipulator\ClassManipulatorManager;
use Bornfight\MabooMakerBundle\Services\Interactor;
use Bornfight\MabooMakerBundle\Services\NamespaceService;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class MakeFixtures extends PlainMaker
{
    public function __construct(
        Interactor $interactor,
        private FixturesClassGenerator $fixturesClassGenerator,
        private NamespaceService $namespaceService,
        private ClassManipulatorManager $manipulatorManager
    ) {
        parent::__construct($interactor);
    }

    public static function getCommandName(): string
    {
        return 'make:maboo-fixtures';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates or updates a fixture class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $this->buildCommand($command)
            ->addModuleArgumentToCommand($command, $inputConfig)
            ->addEntityArgumentToCommand($command, $inputConfig)
            ->addDomainModelArgumentToCommand($command, $inputConfig)
            ->addFixturesArgumentToCommand($command, $inputConfig);
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->interactor->collectFixturesArguments($input, $io, $command);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $module = $input->getArgument($this->interactor->getModuleArg());
        $entity = $input->getArgument($this->interactor->getEntityArg());
        $model = $input->getArgument($this->interactor->getDomainModelArg());
        $fixtures = $input->getArgument($this->interactor->getFixturesArg());

        $fixturesDetails = $generator->createClassNameDetails(
            $fixtures,
            $this->namespaceService->getFixturesNamespace($module)
        );

        $fixturesExist = class_exists($fixturesDetails->getFullName());
        if (true === $fixturesExist) {
            throw new RuntimeCommandException('Updating existing fixtures class is not yet supported!');
        }

        $entityClassDetails = $generator->createClassNameDetails(
            $entity,
            $this->namespaceService->getEntityNamespace()
        );
        $domainModelClassDetails = $generator->createClassNameDetails(
            $model,
            $this->namespaceService->getDomainModelNamespace($module)
        );

        $currentEntityFields = $this->manipulatorManager->getEntityFields($entityClassDetails->getFullName());

        if (false === $fixturesExist) {
            $this->fixturesClassGenerator->generateFixturesClass(
                $fixturesDetails,
                $entityClassDetails,
                $domainModelClassDetails,
                $currentEntityFields
            );

            $generator->writeChanges();

            $this->echoSuccessMessages('Fixtures class generated!', $io);
        }
    }
}
