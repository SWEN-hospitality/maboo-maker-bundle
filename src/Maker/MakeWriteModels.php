<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\ClassGenerator\Domain\WriteModelsClassGenerator;
use Bornfight\MabooMakerBundle\Services\ClassManipulator\ClassManipulatorManager;
use Bornfight\MabooMakerBundle\Services\Interactor;
use Bornfight\MabooMakerBundle\Services\NamespaceService;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class MakeWriteModels extends PlainMaker
{
    private WriteModelsClassGenerator $writeModelsClassGenerator;
    private NamespaceService $namespaceService;
    private ClassManipulatorManager $manipulatorManager;

    public function __construct(
        Interactor $interactor,
        WriteModelsClassGenerator $writeModelsClassGenerator,
        NamespaceService $namespaceService,
        ClassManipulatorManager $manipulatorManager
    ) {
        parent::__construct($interactor);

        $this->writeModelsClassGenerator = $writeModelsClassGenerator;
        $this->namespaceService = $namespaceService;
        $this->manipulatorManager = $manipulatorManager;
    }

    public static function getCommandName(): string
    {
        return 'make:maboo-write-models';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates or updates write models for a model class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $this->buildCommand($command)
            ->addModuleArgumentToCommand($command, $inputConfig)
            ->addEntityArgumentToCommand($command, $inputConfig)
            ->addDomainModelArgumentToCommand($command, $inputConfig)
            ->addCreateWriteModelArgumentToCommand($command, $inputConfig)
            ->addUpdateWriteModelArgumentToCommand($command, $inputConfig);
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $this->interactor->getModule($input, $io, $command);
        $entity = $this->interactor->getEntity($input, $io, $command);
        $domainModel = $this->interactor->getDomainModel($input, $io, $command, $entity);
        $this->interactor->getCreateWriteModel($input, $io, $command, $domainModel);
        $this->interactor->getUpdateWriteModel($input, $io, $command, $domainModel);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $module = $input->getArgument($this->interactor->getModuleArg());
        $entity = $input->getArgument($this->interactor->getEntityArg());
        $model = $input->getArgument($this->interactor->getDomainModelArg());
        $createWriteModel = $input->getArgument($this->interactor->getCreateWriteModelArg());
        $updateWriteModel = $input->getArgument($this->interactor->getUpdateWriteModelArg());

        $createWriteModelClassDetails = $generator->createClassNameDetails(
            $createWriteModel,
            $this->namespaceService->getWriteModelsNamespace($module)
        );
        $updateWriteModelClassDetails = $generator->createClassNameDetails(
            $updateWriteModel,
            $this->namespaceService->getWriteModelsNamespace($module)
        );

        $createWriteModelClassExists = class_exists($createWriteModelClassDetails->getFullName());
        $updateWriteModelClassExists = class_exists($createWriteModelClassDetails->getFullName());
        if (true === $createWriteModelClassExists || true === $updateWriteModelClassExists) {
            throw new RuntimeCommandException('Updating existing write models is not yet supported!');
        }

        if (false === $createWriteModelClassExists) {
            $createWriteModelPath = $this->writeModelsClassGenerator->generateCreateWriteModelClass(
                $createWriteModelClassDetails
            );

            $generator->writeChanges();
        }

        if (false === $updateWriteModelClassExists) {
            $updateWriteModelPath = $this->writeModelsClassGenerator->generateUpdateWriteModelClass(
                $updateWriteModelClassDetails
            );

            $generator->writeChanges();
        }

        $entityClassDetails = $generator->createClassNameDetails(
            $entity,
            $this->namespaceService->getEntityNamespace()
        );
        $currentEntityFields = $this->manipulatorManager->getEntityFields($entityClassDetails->getFullName());

        $createWriteModelManipulator = $this->manipulatorManager->createWriteModelManipulator(
            $createWriteModelClassDetails->getFullName()
        );
        $updateWriteModelManipulator = $this->manipulatorManager->createWriteModelManipulator(
            $updateWriteModelClassDetails->getFullName()
        );

        foreach ($currentEntityFields as $entityField) {
            if (null === $entityField) {
                continue;
            }

            $fileManagerOperations = [];

            $fileManagerOperations[$createWriteModelPath] = $createWriteModelManipulator;
            $createWriteModelManipulator->addField($entityField->name, $entityField->getOptions());

            $fileManagerOperations[$updateWriteModelPath] = $updateWriteModelManipulator;
            $updateWriteModelManipulator->addField($entityField->name, $entityField->getOptions());

            foreach ($fileManagerOperations as $path => $manipulator) {
                $this->manipulatorManager->dumpFile($path, $manipulator->getSourceCode());
            }
        }

        $feedbackMessages = [
            'Success! Write models generated and updated!',
        ];
        $io->block($feedbackMessages, 'OK', 'fg=black;bg=green', ' ', true);
    }
}
