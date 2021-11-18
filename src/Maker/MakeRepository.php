<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\ClassGenerator\Doctrine\RepositoryClassGenerator;
use Bornfight\MabooMakerBundle\Services\ClassGenerator\Domain\RepositoryInterfaceGenerator;
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

class MakeRepository extends PlainMaker
{
    use ClassProperties;

    private RepositoryInterfaceGenerator $repositoryInterfaceGenerator;
    private RepositoryClassGenerator $repositoryClassGenerator;
    private NamespaceService $namespaceService;
    private ClassManipulatorManager $manipulatorManager;

    public function __construct(
        Interactor $interactor,
        RepositoryInterfaceGenerator $repositoryInterfaceGenerator,
        RepositoryClassGenerator $repositoryClassGenerator,
        NamespaceService $namespaceService,
        ClassManipulatorManager $manipulatorManager
    ) {
        parent::__construct($interactor);

        $this->repositoryInterfaceGenerator = $repositoryInterfaceGenerator;
        $this->repositoryClassGenerator = $repositoryClassGenerator;
        $this->namespaceService = $namespaceService;
        $this->manipulatorManager = $manipulatorManager;
    }

    public static function getCommandName(): string
    {
        return 'make:maboo-repository';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates or updates a repository interface and concrete implementation';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $this->buildCommand($command)
            ->addModuleArgumentToCommand($command, $inputConfig)
            ->addEntityArgumentToCommand($command, $inputConfig)
            ->addDomainModelArgumentToCommand($command, $inputConfig)
            ->addCreateWriteModelArgumentToCommand($command, $inputConfig)
            ->addUpdateWriteModelArgumentToCommand($command, $inputConfig)
            ->addEntityMapperArgumentToCommand($command, $inputConfig)
            ->addRepositoryInterfaceArgumentToCommand($command, $inputConfig)
            ->addRepositoryClassArgumentToCommand($command, $inputConfig);
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $this->interactor->collectRepositoryArguments($input, $io, $command);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $module = $input->getArgument($this->interactor->getModuleArg());
        $entity = $input->getArgument($this->interactor->getEntityArg());
        $model = $input->getArgument($this->interactor->getDomainModelArg());
        $createWriteModel = $input->getArgument($this->interactor->getCreateWriteModelArg());
        $updateWriteModel = $input->getArgument($this->interactor->getUpdateWriteModelArg());
        $entityMapper = $input->getArgument($this->interactor->getEntityMapperArg());
        $repositoryInterface = $input->getArgument($this->interactor->getRepositoryInterfaceArg());
        $repositoryClass = $input->getArgument($this->interactor->getRepositoryClassArg());

        $repositoryInterfaceDetails = $generator->createClassNameDetails(
            $repositoryInterface,
            $this->namespaceService->getRepositoryInterfaceNamespace($module)
        );
        $repositoryClassDetails = $generator->createClassNameDetails(
            $repositoryClass,
            $this->namespaceService->getRepositoryClassNamespace($module)
        );

        $repositoryInterfaceExists = interface_exists($repositoryInterfaceDetails->getFullName());
        $repositoryClassExists = class_exists($repositoryClassDetails->getFullName());
        if (true === $repositoryInterfaceExists || true === $repositoryClassExists) {
            throw new RuntimeCommandException('Updating existing repositories is not yet supported!');
        }

        $entityClassDetails = $generator->createClassNameDetails(
            $entity,
            $this->namespaceService->getEntityNamespace()
        );
        $domainModelClassDetails = $generator->createClassNameDetails(
            $model,
            $this->namespaceService->getDomainModelNamespace($module)
        );
        $createWriteModelClassDetails = $generator->createClassNameDetails(
            $createWriteModel,
            $this->namespaceService->getWriteModelsNamespace($module)
        );
        $updateWriteModelClassDetails = $generator->createClassNameDetails(
            $updateWriteModel,
            $this->namespaceService->getWriteModelsNamespace($module)
        );
        $entityMapperClassDetails = $generator->createClassNameDetails(
            $entityMapper,
            $this->namespaceService->getEntityMapperNamespace()
        );

        if (false === $repositoryInterfaceExists) {
            $this->repositoryInterfaceGenerator->generateRepositoryInterface(
                $repositoryInterfaceDetails,
                $domainModelClassDetails,
                $createWriteModelClassDetails,
                $updateWriteModelClassDetails
            );

            $generator->writeChanges();
        }

        $currentEntityFields = $this->manipulatorManager->getEntityFields($entityClassDetails->getFullName());

        if (false === $repositoryClassExists) {
            $this->repositoryClassGenerator->generateRepositoryClass(
                $repositoryClassDetails,
                $repositoryInterfaceDetails,
                $domainModelClassDetails,
                $createWriteModelClassDetails,
                $updateWriteModelClassDetails,
                $entityClassDetails,
                $entityMapperClassDetails,
                $currentEntityFields
            );

            $generator->writeChanges();
        }

        $this->echoSuccessMessages('Repository interface and class generated!', $io);
    }

}
