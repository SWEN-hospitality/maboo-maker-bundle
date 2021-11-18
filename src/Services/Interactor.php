<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services;

use Bornfight\MabooMakerBundle\Maker\DomainModel\Questionnaire as DomainModelQuestionnaire;
use Bornfight\MabooMakerBundle\Maker\Entity\Questionnaire as EntityQuestionnaire;
use Bornfight\MabooMakerBundle\Maker\EntityMapper\Questionnaire as EntityMapperQuestionnaire;
use Bornfight\MabooMakerBundle\Maker\Fixtures\Questionnaire as FixturesQuestionnaire;
use Bornfight\MabooMakerBundle\Maker\Manager\Questionnaire as ManagerQuestionnaire;
use Bornfight\MabooMakerBundle\Maker\Module\Questionnaire as ModuleQuestionnaire;
use Bornfight\MabooMakerBundle\Maker\Mutation\Questionnaire as MutationQuestionnaire;
use Bornfight\MabooMakerBundle\Maker\Repository\Questionnaire as RepositoryQuestionnaire;
use Bornfight\MabooMakerBundle\Maker\Resolver\Questionnaire as ResolverQuestionnaire;
use Bornfight\MabooMakerBundle\Maker\Validator\Questionnaire as ValidatorQuestionnaire;
use Bornfight\MabooMakerBundle\Maker\WriteModels\Questionnaire as WriteModelsQuestionnaire;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class Interactor
{
    public const MODULE_ARG = 'module';
    public const ENTITY_ARG = 'entity';
    public const MODEL_ARG = 'model';
    public const CREATE_WRITE_MODEL_ARG = 'create_write_model';
    public const UPDATE_WRITE_MODEL_ARG = 'update_write_model';
    public const ENTITY_MAPPER_ARG = 'entity_mapper';
    public const REPOSITORY_INTERFACE_ARG = 'repo_interface';
    public const REPOSITORY_CLASS_ARG = 'repo_class';
    public const VALIDATOR_ARG = 'validator';
    public const SPECIFICATION_INTERFACE_ARG = 'spec_interface';
    public const SPECIFICATION_CLASS_ARG = 'spec_class';
    public const MANAGER_ARG = 'manager';
    public const RESOLVER_ARG = 'resolver';
    public const MUTATION_ARG = 'mutation';
    public const FIXTURES_ARG = 'fixtures';

    private ModuleQuestionnaire $moduleQuestionnaire;
    private EntityQuestionnaire $entityQuestionnaire;
    private DomainModelQuestionnaire $domainModelQuestionnaire;
    private WriteModelsQuestionnaire $writeModelsQuestionnaire;
    private EntityMapperQuestionnaire $entityMapperQuestionnaire;
    private RepositoryQuestionnaire $repositoryQuestionnaire;
    private ValidatorQuestionnaire $validatorQuestionnaire;
    private ManagerQuestionnaire $managerQuestionnaire;
    private ResolverQuestionnaire $resolverQuestionnaire;
    private MutationQuestionnaire $mutationQuestionnaire;
    private FixturesQuestionnaire $fixturesQuestionnaire;

    public function __construct(
        ModuleQuestionnaire $moduleQuestionnaire,
        EntityQuestionnaire $entityQuestionnaire,
        DomainModelQuestionnaire $domainModelQuestionnaire,
        WriteModelsQuestionnaire $writeModelsQuestionnaire,
        EntityMapperQuestionnaire $entityMapperQuestionnaire,
        RepositoryQuestionnaire $repositoryQuestionnaire,
        ValidatorQuestionnaire $validatorQuestionnaire,
        ManagerQuestionnaire $managerQuestionnaire,
        ResolverQuestionnaire $resolverQuestionnaire,
        MutationQuestionnaire $mutationQuestionnaire,
        FixturesQuestionnaire $fixturesQuestionnaire
    ) {
        $this->moduleQuestionnaire = $moduleQuestionnaire;
        $this->entityQuestionnaire = $entityQuestionnaire;
        $this->domainModelQuestionnaire = $domainModelQuestionnaire;
        $this->writeModelsQuestionnaire = $writeModelsQuestionnaire;
        $this->entityMapperQuestionnaire = $entityMapperQuestionnaire;
        $this->repositoryQuestionnaire = $repositoryQuestionnaire;
        $this->validatorQuestionnaire = $validatorQuestionnaire;
        $this->managerQuestionnaire = $managerQuestionnaire;
        $this->resolverQuestionnaire = $resolverQuestionnaire;
        $this->mutationQuestionnaire = $mutationQuestionnaire;
        $this->fixturesQuestionnaire = $fixturesQuestionnaire;
    }

    public function collectManagerArguments(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->getModule($input, $io, $command);
        $entity = $this->getEntity($input, $io, $command);
        $domainModel = $this->getDomainModel($input, $io, $command, $entity);
        $this->getCreateWriteModel($input, $io, $command, $domainModel);
        $this->getUpdateWriteModel($input, $io, $command, $domainModel);
        $this->getRepositoryInterface($input, $io, $command, $domainModel);
        $this->getValidator($input, $io, $command, $domainModel);
        $this->getManager($input, $io, $command, $domainModel);
    }

    public function collectResolverArguments(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->getModule($input, $io, $command);
        $entity = $this->getEntity($input, $io, $command);
        $domainModel = $this->getDomainModel($input, $io, $command, $entity);
        $this->getRepositoryInterface($input, $io, $command, $domainModel);
        $this->getResolver($input, $io, $command, $domainModel);
    }

    public function collectMutationArguments(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->getModule($input, $io, $command);
        $entity = $this->getEntity($input, $io, $command);
        $domainModel = $this->getDomainModel($input, $io, $command, $entity);
        $this->getManager($input, $io, $command, $domainModel);
        $this->getMutation($input, $io, $command, $domainModel);
    }

    public function collectFixturesArguments(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->getModule($input, $io, $command);
        $entity = $this->getEntity($input, $io, $command);
        $domainModel = $this->getDomainModel($input, $io, $command, $entity);
        $this->getFixtures($input, $io, $command, $domainModel);
    }

    public function getModuleArg(): string
    {
        return self::MODULE_ARG;
    }

    public function getModule(InputInterface $input, ConsoleStyle $io, Command $command): string
    {
        $module = $input->getArgument($this->getModuleArg());

        if (true === is_string($module) && '' !== $module) {
            return $module;
        }

        $argument = $command->getDefinition()->getArgument($this->getModuleArg());
        $module = $this->moduleQuestionnaire->getModule($io, $argument->getDescription());

        $input->setArgument($this->getModuleArg(), $module);

        return $module;
    }

    public function getEntityArg(): string
    {
        return self::ENTITY_ARG;
    }

    public function getEntity(InputInterface $input, ConsoleStyle $io, Command $command): string
    {
        $entity = $input->getArgument($this->getEntityArg());

        if (true === is_string($entity) && '' !== $entity) {
            return $entity;
        }

        $argument = $command->getDefinition()->getArgument($this->getEntityArg());
        $entity = $this->entityQuestionnaire->getEntityClassName($io, $argument->getDescription());

        $input->setArgument($this->getEntityArg(), $entity);

        return $entity;
    }

    public function getDomainModelArg(): string
    {
        return self::MODEL_ARG;
    }

    public function getDomainModel(InputInterface $input, ConsoleStyle $io, Command $command, ?string $entity): string
    {
        $model = $input->getArgument($this->getDomainModelArg());

        if (true === is_string($model) && '' !== $model) {
            return $model;
        }

        $argument = $command->getDefinition()->getArgument($this->getDomainModelArg());
        $model = $this->domainModelQuestionnaire->getModelClassName($io, $argument->getDescription(), $entity);

        $input->setArgument($this->getDomainModelArg(), $model);

        return $model;
    }

    public function getCreateWriteModelArg(): string
    {
        return self::CREATE_WRITE_MODEL_ARG;
    }

    public function getUpdateWriteModelArg(): string
    {
        return self::UPDATE_WRITE_MODEL_ARG;
    }

    public function getCreateWriteModel(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $domainModel
    ): string {
        $createWriteModel = $input->getArgument($this->getCreateWriteModelArg());

        if (true === is_string($createWriteModel) && '' !== $createWriteModel) {
            return $createWriteModel;
        }

        $argument = $command->getDefinition()->getArgument($this->getCreateWriteModelArg());
        $createWriteModel = $this->writeModelsQuestionnaire->getCreateModelClassName(
            $io,
            $argument->getDescription(),
            $domainModel
        );

        $input->setArgument($this->getCreateWriteModelArg(), $createWriteModel);

        return $createWriteModel;
    }

    public function getUpdateWriteModel(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $domainModel
    ): string {
        $updateWriteModel = $input->getArgument($this->getUpdateWriteModelArg());

        if (true === is_string($updateWriteModel) && '' !== $updateWriteModel) {
            return $updateWriteModel;
        }

        $argument = $command->getDefinition()->getArgument($this->getUpdateWriteModelArg());
        $updateWriteModel = $this->writeModelsQuestionnaire->getUpdateModelClassName(
            $io,
            $argument->getDescription(),
            $domainModel
        );

        $input->setArgument($this->getUpdateWriteModelArg(), $updateWriteModel);

        return $updateWriteModel;
    }

    public function getEntityMapperArg(): string
    {
        return self::ENTITY_MAPPER_ARG;
    }

    public function getEntityMapper(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $entity
    ): string {
        $entityMapper = $input->getArgument($this->getEntityMapperArg());

        if (true === is_string($entityMapper) && '' !== $entityMapper) {
            return $entityMapper;
        }

        $argument = $command->getDefinition()->getArgument($this->getEntityMapperArg());
        $entityMapper = $this->entityMapperQuestionnaire->getEntityMapperClassName(
            $io,
            $argument->getDescription(),
            $entity
        );

        $input->setArgument($this->getEntityMapperArg(), $entityMapper);

        return $entityMapper;
    }

    public function getRepositoryInterfaceArg(): string
    {
        return self::REPOSITORY_INTERFACE_ARG;
    }

    public function getRepositoryClassArg(): string
    {
        return self::REPOSITORY_CLASS_ARG;
    }

    public function getRepositoryInterface(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $domainModel
    ): string {
        $repositoryInterface = $input->getArgument($this->getRepositoryInterfaceArg());

        if (true === is_string($repositoryInterface) && '' !== $repositoryInterface) {
            return $repositoryInterface;
        }

        $argument = $command->getDefinition()->getArgument($this->getRepositoryInterfaceArg());
        $repositoryInterface = $this->repositoryQuestionnaire->getRepositoryInterfaceName(
            $io,
            $argument->getDescription(),
            $domainModel
        );

        $input->setArgument($this->getRepositoryInterfaceArg(), $repositoryInterface);

        return $repositoryInterface;
    }

    public function getRepositoryClass(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $repositoryInterface
    ): string {
        $repositoryClass = $input->getArgument($this->getRepositoryClassArg());

        if (true === is_string($repositoryClass) && '' !== $repositoryClass) {
            return $repositoryClass;
        }

        $argument = $command->getDefinition()->getArgument($this->getRepositoryClassArg());
        $repositoryClass = $this->repositoryQuestionnaire->getRepositoryClassName(
            $io,
            $argument->getDescription(),
            $repositoryInterface
        );

        $input->setArgument($this->getRepositoryClassArg(), $repositoryClass);

        return $repositoryClass;
    }

    public function getManagerArg(): string
    {
        return self::MANAGER_ARG;
    }

    public function getManager(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $domainModel
    ): string {
        $manager = $input->getArgument($this->getManagerArg());

        if (true === is_string($manager) && '' !== $manager) {
            return $manager;
        }

        $argument = $command->getDefinition()->getArgument($this->getManagerArg());
        $manager = $this->managerQuestionnaire->getManagerClassName(
            $io,
            $argument->getDescription(),
            $domainModel
        );

        $input->setArgument($this->getManagerArg(), $manager);

        return $manager;
    }

    public function getSpecificationInterfaceArg(): string
    {
        return self::SPECIFICATION_INTERFACE_ARG;
    }

    public function getSpecificationClassArg(): string
    {
        return self::SPECIFICATION_CLASS_ARG;
    }

    public function getSpecificationInterface(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $domainModel
    ): string {
        $specificationInterface = $input->getArgument($this->getSpecificationInterfaceArg());

        if (true === is_string($specificationInterface) && '' !== $specificationInterface) {
            return $specificationInterface;
        }

        $argument = $command->getDefinition()->getArgument($this->getSpecificationInterfaceArg());
        $specificationInterface = $this->validatorQuestionnaire->getSpecificationInterfaceName(
            $io,
            $argument->getDescription(),
            $domainModel
        );

        $input->setArgument($this->getSpecificationInterfaceArg(), $specificationInterface);

        return $specificationInterface;
    }

    public function getSpecificationClass(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $specificationInterface
    ): string {
        $specificationClass = $input->getArgument($this->getSpecificationClassArg());

        if (true === is_string($specificationClass) && '' !== $specificationClass) {
            return $specificationClass;
        }

        $argument = $command->getDefinition()->getArgument($this->getSpecificationClassArg());
        $specificationClass = $this->repositoryQuestionnaire->getRepositoryClassName(
            $io,
            $argument->getDescription(),
            $specificationInterface
        );

        $input->setArgument($this->getSpecificationClassArg(), $specificationClass);

        return $specificationClass;
    }

    public function getValidatorArg(): string
    {
        return self::VALIDATOR_ARG;
    }

    public function getValidator(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $domainModel
    ): string {
        $validator = $input->getArgument($this->getValidatorArg());

        if (true === is_string($validator) && '' !== $validator) {
            return $validator;
        }

        $argument = $command->getDefinition()->getArgument($this->getValidatorArg());
        $validator = $this->validatorQuestionnaire->getValidatorClassName(
            $io,
            $argument->getDescription(),
            $domainModel
        );

        $input->setArgument($this->getValidatorArg(), $validator);

        return $validator;
    }

    public function getResolverArg(): string
    {
        return self::RESOLVER_ARG;
    }

    public function getResolver(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $domainModel
    ): string {
        $resolver = $input->getArgument($this->getResolverArg());

        if (true === is_string($resolver) && '' !== $resolver) {
            return $resolver;
        }

        $argument = $command->getDefinition()->getArgument($this->getResolverArg());
        $resolver = $this->resolverQuestionnaire->getResolverClassName(
            $io,
            $argument->getDescription(),
            $domainModel
        );

        $input->setArgument($this->getResolverArg(), $resolver);

        return $resolver;
    }

    public function getMutationArg(): string
    {
        return self::MUTATION_ARG;
    }

    public function getMutation(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $domainModel
    ): string {
        $mutation = $input->getArgument($this->getMutationArg());

        if (true === is_string($mutation) && '' !== $mutation) {
            return $mutation;
        }

        $argument = $command->getDefinition()->getArgument($this->getMutationArg());
        $mutation = $this->mutationQuestionnaire->getMutationClassName(
            $io,
            $argument->getDescription(),
            $domainModel
        );

        $input->setArgument($this->getMutationArg(), $mutation);

        return $mutation;
    }

    public function getFixturesArg(): string
    {
        return self::FIXTURES_ARG;
    }

    public function getFixtures(
        InputInterface $input,
        ConsoleStyle $io,
        Command $command,
        string $domainModel
    ): string {
        $fixtures = $input->getArgument($this->getFixturesArg());

        if (true === is_string($fixtures) && '' !== $fixtures) {
            return $fixtures;
        }

        $argument = $command->getDefinition()->getArgument($this->getFixturesArg());
        $fixtures = $this->fixturesQuestionnaire->getFixturesClassName(
            $io,
            $argument->getDescription(),
            $domainModel
        );

        $input->setArgument($this->getFixturesArg(), $fixtures);

        return $fixtures;
    }
}
