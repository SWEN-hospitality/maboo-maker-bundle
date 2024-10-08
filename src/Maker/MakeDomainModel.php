<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\ClassGenerator\Domain\DomainModelClassGenerator;
use Bornfight\MabooMakerBundle\Services\ClassManipulator\ClassManipulatorManager;
use Bornfight\MabooMakerBundle\Services\Interactor;
use Bornfight\MabooMakerBundle\Services\NamespaceService;
use Bornfight\MabooMakerBundle\Util\ClassProperties;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class MakeDomainModel extends PlainMaker
{
    use ClassProperties;

    public function __construct(
        Interactor $interactor,
        private DomainModelClassGenerator $domainModelClassGenerator,
        private NamespaceService $namespaceService,
        private ClassManipulatorManager $manipulatorManager
    ) {
        parent::__construct($interactor);
    }

    public static function getCommandName(): string
    {
        return 'make:maboo-domain-model';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates or updates a domain model class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $this->buildCommand($command)
            ->addModuleArgumentToCommand($command, $inputConfig)
            ->addEntityArgumentToCommand($command, $inputConfig)
            ->addDomainModelArgumentToCommand($command, $inputConfig);
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->interactor->collectDomainModelArguments($input, $io, $command);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $module = $input->getArgument($this->interactor->getModuleArg());
        $entity = $input->getArgument($this->interactor->getEntityArg());
        $model = $input->getArgument($this->interactor->getDomainModelArg());

        $domainModelClassDetails = $generator->createClassNameDetails(
            $model,
            $this->namespaceService->getDomainModelNamespace($module)
        );
        $domainModelFullName = $domainModelClassDetails->getFullName();

        $classExists = class_exists($domainModelFullName);

        if (false === $classExists) {
            $domainModelPath = $this->domainModelClassGenerator->generateDomainModelClass(
                $domainModelClassDetails
            );

            $generator->writeChanges();

            $this->echoSuccessMessages('Domain model generated!', $io);
        } else {
            $domainModelPath = $this->getPathOfClass($domainModelFullName);
        }

        $entityClassDetails = $generator->createClassNameDetails(
            $entity,
            $this->namespaceService->getEntityNamespace()
        );
        $currentEntityFields = $this->manipulatorManager->getEntityFields($entityClassDetails->getFullName());


        $domainModelManipulator = $this->manipulatorManager->createDomainModelManipulator($domainModelFullName);
        $currentDomainModelFields = $domainModelManipulator->getAllFields();

        foreach ($currentEntityFields as $entityField) {
            if (null === $entityField) {
                continue;
            }

            if (true === $this->isFieldAlreadyInClass($currentDomainModelFields, $entityField)) {
                continue;
            }

            if (false === $entityField->isOfAddableType()) {
                continue;
            }

            $fileManagerOperations = [];
            $fileManagerOperations[$domainModelPath] = $domainModelManipulator;

            if ($entityField->isManyToOneField()) {
                $domainModelManipulator->addForeignKeyField($entityField->foreignKeyName(), $entityField->getOptions());
            } else {
                $domainModelManipulator->addField($entityField->name, $entityField->getOptions());
            }


            foreach ($fileManagerOperations as $path => $manipulatorOrMessage) {
                $this->manipulatorManager->dumpFile($path, $manipulatorOrMessage->getSourceCode());
            }
        }

        $this->echoSuccessMessages('Domain model updated!', $io);
    }
}
