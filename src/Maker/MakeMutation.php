<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\ClassGenerator\Infrastructure\MutationClassGenerator;
use Bornfight\MabooMakerBundle\Services\Interactor;
use Bornfight\MabooMakerBundle\Services\NamespaceService;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class MakeMutation extends PlainMaker
{
    private MutationClassGenerator $mutationClassGenerator;
    private NamespaceService $namespaceService;

    public function __construct(
        Interactor $interactor,
        MutationClassGenerator $mutationClassGenerator,
        NamespaceService $namespaceService
    ) {
        parent::__construct($interactor);

        $this->mutationClassGenerator = $mutationClassGenerator;
        $this->namespaceService = $namespaceService;
    }

    public static function getCommandName(): string
    {
        return 'make:maboo-mutation';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates or updates a mutation class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $this->buildCommand($command)
            ->addModuleArgumentToCommand($command, $inputConfig)
            ->addEntityArgumentToCommand($command, $inputConfig)
            ->addDomainModelArgumentToCommand($command, $inputConfig)
            ->addManagerArgumentToCommand($command, $inputConfig)
            ->addMutationArgumentToCommand($command, $inputConfig);
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $this->interactor->collectMutationArguments($input, $io, $command);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $module = $input->getArgument($this->interactor->getModuleArg());
        $model = $input->getArgument($this->interactor->getDomainModelArg());
        $manager = $input->getArgument($this->interactor->getManagerArg());
        $mutation = $input->getArgument($this->interactor->getMutationArg());

        $mutationDetails = $generator->createClassNameDetails(
            $mutation,
            $this->namespaceService->getMutationNamespace($module)
        );

        $mutationExists = class_exists($mutationDetails->getFullName());
        if (true === $mutationExists) {
            throw new RuntimeCommandException('Updating existing mutation class is not yet supported!');
        }

        $domainModelClassDetails = $generator->createClassNameDetails(
            $model,
            $this->namespaceService->getDomainModelNamespace($module)
        );
        $managerDetails = $generator->createClassNameDetails(
            $manager,
            $this->namespaceService->getManagerNamespace($module)
        );

        if (false === $mutationExists) {
            $this->mutationClassGenerator->generateMutationClass(
                $mutationDetails,
                $managerDetails,
                $domainModelClassDetails
            );

            $generator->writeChanges();

            $this->echoSuccessMessages('Mutation class generated!', $io);
        }
    }
}
