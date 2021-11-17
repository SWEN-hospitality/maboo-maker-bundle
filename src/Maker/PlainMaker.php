<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\Interactor;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

abstract class PlainMaker extends AbstractMaker
{
    protected Interactor $interactor;

    public function __construct(Interactor $interactor)
    {
        $this->interactor = $interactor;
    }

    public function configureDependencies(DependencyBuilder $dependencies, InputInterface $input = null)
    {
    }

    protected function buildCommand(Command $command): self
    {
        $command->setDescription($this->getCommandDescription());

        return $this;
    }

    protected function addModuleArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getModuleArg(),
            InputArgument::OPTIONAL,
            'Module (bounded context) in which domain model resides'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getModuleArg());

        return $this;
    }

    protected function addEntityArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getEntityArg(),
            InputArgument::OPTIONAL,
            'Entity class'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getEntityArg());

        return $this;
    }

    protected function addDomainModelArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getDomainModelArg(),
            InputArgument::OPTIONAL,
            'Domain model name (doesn\'t have to be the same as entity name)'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getDomainModelArg());

        return $this;
    }

    protected function addCreateWriteModelArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getCreateWriteModelArg(),
            InputArgument::OPTIONAL,
            'Create write model name'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getCreateWriteModelArg());

        return $this;
    }

    protected function addUpdateWriteModelArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getUpdateWriteModelArg(),
            InputArgument::OPTIONAL,
            'Update write model name'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getUpdateWriteModelArg());

        return $this;
    }

    protected function addEntityMapperArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getEntityMapperArg(),
            InputArgument::OPTIONAL,
            'Entity mapper class name'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getEntityMapperArg());

        return $this;
    }

    protected function addRepositoryInterfaceArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getRepositoryInterfaceArg(),
            InputArgument::OPTIONAL,
            'Repository interface name'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getRepositoryInterfaceArg());

        return $this;
    }

    protected function addRepositoryClassArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getRepositoryClassArg(),
            InputArgument::OPTIONAL,
            'Repository class name'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getRepositoryClassArg());

        return $this;
    }

    protected function addValidatorArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getValidatorArg(),
            InputArgument::OPTIONAL,
            'Validator name'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getValidatorArg());

        return $this;
    }

    protected function addSpecificationInterfaceArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getSpecificationInterfaceArg(),
            InputArgument::OPTIONAL,
            'Specification interface name'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getSpecificationInterfaceArg());

        return $this;
    }

    protected function addSpecificationClassArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getSpecificationClassArg(),
            InputArgument::OPTIONAL,
            'Specification class name'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getSpecificationClassArg());

        return $this;
    }

    protected function addManagerArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getManagerArg(),
            InputArgument::OPTIONAL,
            'Manager name'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getManagerArg());

        return $this;
    }

    protected function addResolverArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getResolverArg(),
            InputArgument::OPTIONAL,
            'Resolver name'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getResolverArg());

        return $this;
    }

    protected function addMutationArgumentToCommand(Command $command, InputConfiguration $inputConfig): self
    {
        $command->addArgument(
            $this->interactor->getMutationArg(),
            InputArgument::OPTIONAL,
            'Mutation class name'
        );
        $inputConfig->setArgumentAsNonInteractive($this->interactor->getMutationArg());

        return $this;
    }
}
