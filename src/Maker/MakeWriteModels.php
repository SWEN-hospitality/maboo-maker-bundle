<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\ClassGenerator\Domain\WriteModelsClassGenerator;
use Bornfight\MabooMakerBundle\Services\ClassManipulator\ClassManipulatorManager;
use Bornfight\MabooMakerBundle\Services\Interactor;
use Bornfight\MabooMakerBundle\Services\NamespaceService;
use Bornfight\MabooMakerBundle\Util\ClassProperties;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class MakeWriteModels extends PlainMaker
{
    use ClassProperties;

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
        $this->interactor->collectWriteModelsArguments($input, $io, $command);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $module = $input->getArgument($this->interactor->getModuleArg());
        $entity = $input->getArgument($this->interactor->getEntityArg());
        $input->getArgument($this->interactor->getDomainModelArg());
        $createWriteModel = $input->getArgument($this->interactor->getCreateWriteModelArg());
        $updateWriteModel = $input->getArgument($this->interactor->getUpdateWriteModelArg());

        $createWriteModelClassDetails = $generator->createClassNameDetails(
            $createWriteModel,
            $this->namespaceService->getWriteModelsNamespace($module)
        );
        $createWriteModelFullName = $createWriteModelClassDetails->getFullName();
        $updateWriteModelClassDetails = $generator->createClassNameDetails(
            $updateWriteModel,
            $this->namespaceService->getWriteModelsNamespace($module)
        );
        $updateWriteModelFullName = $updateWriteModelClassDetails->getFullName();

        $createWriteModelClassExists = class_exists($createWriteModelFullName);
        $updateWriteModelClassExists = class_exists($updateWriteModelFullName);

        if (false === $createWriteModelClassExists) {
            $createWriteModelPath = $this->writeModelsClassGenerator->generateCreateWriteModelClass(
                $createWriteModelClassDetails
            );

            $generator->writeChanges();

            $this->echoSuccessMessages('Create write model generated!', $io);
        } else {
            $createWriteModelPath = $this->getPathOfClass($createWriteModelFullName);
        }

        if (false === $updateWriteModelClassExists) {
            $updateWriteModelPath = $this->writeModelsClassGenerator->generateUpdateWriteModelClass(
                $updateWriteModelClassDetails
            );

            $generator->writeChanges();

            $this->echoSuccessMessages('Update write model generated!', $io);
        } else {
            $updateWriteModelPath = $this->getPathOfClass($updateWriteModelFullName);
        }

        $entityClassDetails = $generator->createClassNameDetails(
            $entity,
            $this->namespaceService->getEntityNamespace()
        );
        $currentEntityFields = $this->manipulatorManager->getEntityFields($entityClassDetails->getFullName());

        $createWriteModelManipulator = $this->manipulatorManager->createWriteModelManipulator($createWriteModelFullName);
        $updateWriteModelManipulator = $this->manipulatorManager->createWriteModelManipulator($updateWriteModelFullName);
        $currentCreateWriteModelFields = $createWriteModelManipulator->getAllFields();
        $currentUpdateWriteModelFields = $updateWriteModelManipulator->getAllFields();

        foreach ($currentEntityFields as $entityField) {
            if (null === $entityField) {
                continue;
            }

            $fileManagerOperations = [];

            $shouldAddToCreateWriteModel =
                false === $this->isFieldAlreadyInClass($currentCreateWriteModelFields, $entityField) &&
                true === $entityField->isOfAddableType();
            $shouldAddToUpdateWriteModel =
                false === $this->isFieldAlreadyInClass($currentUpdateWriteModelFields, $entityField) &&
                true === $entityField->isOfAddableType();

            if ($shouldAddToCreateWriteModel) {
                $fileManagerOperations[$createWriteModelPath] = $createWriteModelManipulator;
                $createWriteModelManipulator->addField($entityField->name, $entityField->getOptions());
            }

            if ($shouldAddToUpdateWriteModel) {
                $fileManagerOperations[$updateWriteModelPath] = $updateWriteModelManipulator;
                $updateWriteModelManipulator->addField($entityField->name, $entityField->getOptions());
            }

            foreach ($fileManagerOperations as $path => $manipulator) {
                $this->manipulatorManager->dumpFile($path, $manipulator->getSourceCode());
            }
        }

        $this->echoSuccessMessages('Write models updated!', $io);
    }
}
