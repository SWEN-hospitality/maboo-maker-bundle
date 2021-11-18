<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\ClassGenerator\Infrastructure\ResolverClassGenerator;
use Bornfight\MabooMakerBundle\Services\EntityNamingService;
use Bornfight\MabooMakerBundle\Services\Interactor;
use Bornfight\MabooMakerBundle\Services\NamespaceService;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class MakeResolver extends PlainMaker
{
    private ResolverClassGenerator $resolverClassGenerator;
    private NamespaceService $namespaceService;
    private EntityNamingService $entityNaming;

    public function __construct(
        Interactor $interactor,
        ResolverClassGenerator $resolverClassGenerator,
        NamespaceService $namespaceService,
        EntityNamingService $entityNaming
    ) {
        parent::__construct($interactor);

        $this->resolverClassGenerator = $resolverClassGenerator;
        $this->namespaceService = $namespaceService;
        $this->entityNaming = $entityNaming;
    }

    public static function getCommandName(): string
    {
        return 'make:maboo-resolver';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates or updates a resolver';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $this->buildCommand($command)
            ->addModuleArgumentToCommand($command, $inputConfig)
            ->addDomainModelArgumentToCommand($command, $inputConfig)
            ->addRepositoryInterfaceArgumentToCommand($command, $inputConfig)
            ->addResolverArgumentToCommand($command, $inputConfig);
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $this->interactor->collectResolverArguments($input, $io, $command);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $module = $input->getArgument($this->interactor->getModuleArg());
        $model = $input->getArgument($this->interactor->getDomainModelArg());
        $repositoryInterface = $input->getArgument($this->interactor->getRepositoryInterfaceArg());
        $resolver = $input->getArgument($this->interactor->getResolverArg());

        $resolverDetails = $generator->createClassNameDetails(
            $resolver,
            $this->namespaceService->getResolverNamespace($module)
        );

        $resolverExists = class_exists($resolverDetails->getFullName());
        if (true === $resolverExists) {
            throw new RuntimeCommandException('Updating existing resolvers is not yet supported!');
        }

        $domainModelClassDetails = $generator->createClassNameDetails(
            $model,
            $this->namespaceService->getDomainModelNamespace($module)
        );
        $repositoryInterfaceDetails = $generator->createClassNameDetails(
            $repositoryInterface,
            $this->namespaceService->getRepositoryInterfaceNamespace($module)
        );

        $domainModel = $domainModelClassDetails->getShortName();

        $entityVarSingular = $this->entityNaming->getSingularName($domainModel);
        $entityVarPlural = $this->entityNaming->getPluralName($domainModel);

        if (false === $resolverExists) {
            $this->resolverClassGenerator->generateResolverClass(
                $resolverDetails,
                $repositoryInterfaceDetails,
                $domainModelClassDetails,
                $entityVarSingular,
                $entityVarPlural
            );

            $generator->writeChanges();

            $this->echoSuccessMessages('Resolver generated!', $io);
        }
    }
}
