<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\ClassGenerator\Application\SpecificationInterfaceGenerator;
use Bornfight\MabooMakerBundle\Services\ClassGenerator\Application\ValidatorClassGenerator;
use Bornfight\MabooMakerBundle\Services\ClassGenerator\Doctrine\SpecificationClassGenerator;
use Bornfight\MabooMakerBundle\Services\ClassManipulator\ClassManipulatorManager;
use Bornfight\MabooMakerBundle\Services\Interactor;
use Bornfight\MabooMakerBundle\Services\NamespaceService;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class MakeValidator extends PlainMaker
{
    public function __construct(
        Interactor $interactor,
        private ValidatorClassGenerator $validatorClassGenerator,
        private SpecificationInterfaceGenerator $specificationInterfaceGenerator,
        private SpecificationClassGenerator $specificationClassGenerator,
        private NamespaceService $namespaceService,
        private ClassManipulatorManager $manipulatorManager
    ) {
        parent::__construct($interactor);
    }

    public static function getCommandName(): string
    {
        return 'make:maboo-validator';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates or updates a validator and specification';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $this->buildCommand($command)
            ->addModuleArgumentToCommand($command, $inputConfig)
            ->addEntityArgumentToCommand($command, $inputConfig)
            ->addDomainModelArgumentToCommand($command, $inputConfig)
            ->addRepositoryInterfaceArgumentToCommand($command, $inputConfig)
            ->addSpecificationInterfaceArgumentToCommand($command, $inputConfig)
            ->addSpecificationClassArgumentToCommand($command, $inputConfig)
            ->addValidatorArgumentToCommand($command, $inputConfig);
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->interactor->collectValidatorArguments($input, $io, $command);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $module = $input->getArgument($this->interactor->getModuleArg());
        $model = $input->getArgument($this->interactor->getDomainModelArg());
        $repository = $input->getArgument($this->interactor->getRepositoryInterfaceArg());
        $specificationInterface = $input->getArgument($this->interactor->getSpecificationInterfaceArg());
        $specificationClass = $input->getArgument($this->interactor->getSpecificationClassArg());
        $validator = $input->getArgument($this->interactor->getValidatorArg());

        $specificationInterfaceDetails = $generator->createClassNameDetails(
            $specificationInterface,
            $this->namespaceService->getSpecificationInterfaceNamespace($module)
        );
        $specificationClassDetails = $generator->createClassNameDetails(
            $specificationClass,
            $this->namespaceService->getSpecificationClassNamespace($module)
        );

        $validatorDetails = $generator->createClassNameDetails(
            $validator,
            $this->namespaceService->getValidatorNamespace($module)
        );

        $validatorExists = class_exists($validatorDetails->getFullName());
        if (true === $validatorExists) {
            throw new RuntimeCommandException('Updating existing validators is not yet supported!');
        }

        $repositoryDetails = $generator->createClassNameDetails(
            $repository,
            $this->namespaceService->getRepositoryInterfaceNamespace($module)
        );

        $specificationInterfaceExists = interface_exists($specificationInterfaceDetails->getFullName());
        $specificationClassExists = class_exists($specificationClassDetails->getFullName());

        if (false === $specificationInterfaceExists) {
            $this->specificationInterfaceGenerator->generateSpecificationInterface(
                $specificationInterfaceDetails
            );

            $generator->writeChanges();
        }

        if (false === $specificationClassExists) {
            $this->specificationClassGenerator->generateSpecificationClass(
                $specificationClassDetails,
                $specificationInterfaceDetails,
                $repositoryDetails
            );

            $generator->writeChanges();
        }

        $domainModelClassDetails = $generator->createClassNameDetails(
            $model,
            $this->namespaceService->getDomainModelNamespace($module)
        );

        $domainModelFields = $this->manipulatorManager->getDomainModelFields($domainModelClassDetails->getFullName());

        if (false === $validatorExists) {
            $this->validatorClassGenerator->generateValidatorClass(
                $validatorDetails,
                $domainModelClassDetails,
                $specificationInterfaceDetails,
                $domainModelFields
            );

            $generator->writeChanges();

            $this->echoSuccessMessages('Validator generated!', $io);
        }
    }

}
