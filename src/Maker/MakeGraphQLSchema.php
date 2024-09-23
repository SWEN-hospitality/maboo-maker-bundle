<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\ClassGenerator\Infrastructure\GraphQLSchemaGenerator;
use Bornfight\MabooMakerBundle\Services\ClassManipulator\ClassManipulatorManager;
use Bornfight\MabooMakerBundle\Services\ClassManipulator\EntityField;
use Bornfight\MabooMakerBundle\Services\EntityNamingService;
use Bornfight\MabooMakerBundle\Services\Interactor;
use Bornfight\MabooMakerBundle\Services\NamespaceService;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Util\YamlManipulationFailedException;
use Symfony\Bundle\MakerBundle\Util\YamlSourceManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class MakeGraphQLSchema extends PlainMaker
{
    public function __construct(
        Interactor $interactor,
        private GraphQLSchemaGenerator $graphQLSchemaGenerator,
        private NamespaceService $namespaceService,
        private ClassManipulatorManager $manipulatorManager,
        private EntityNamingService $entityNaming
    ) {
        parent::__construct($interactor);
    }

    public static function getCommandName(): string
    {
        return 'make:maboo-gql-schema';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates or updates GraphQL types and schema';
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
        $this->interactor->collectGraphQLSchemaArguments($input, $io, $command);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $module = $input->getArgument($this->interactor->getModuleArg());
        $model = $input->getArgument($this->interactor->getDomainModelArg());

        $domainModelClassDetails = $generator->createClassNameDetails(
            $model,
            $this->namespaceService->getDomainModelNamespace($module)
        );
        $domainModelFields = $this->manipulatorManager->getEntityFields($domainModelClassDetails->getFullName());

        $domainModel = $domainModelClassDetails->getShortName();


        $this->generateModelTypeSchema($generator, $module, $domainModel, $domainModelFields);
        $this->generateCreateMutationInputTypeSchema($generator, $module, $domainModel, $domainModelFields);
        $this->generateCreateMutationPayloadTypeSchema($module, $domainModel);
        $this->generateUpdateMutationInputTypeSchema($generator, $module, $domainModel, $domainModelFields);
        $this->generateUpdateMutationPayloadTypeSchema($module, $domainModel);
        $this->generateDeleteMutationInputTypeSchema($module, $domainModel);
        $this->generateDeleteMutationPayloadTypeSchema($module, $domainModel);

        $this->registerMutations($generator, $domainModel);
        $this->registerQueries($generator, $domainModel);

        $this->echoSuccessMessages('GraphQL schema updated!', $io);
    }

    /**
     * @param EntityField[] $domainModelFields
     */
    private function generateModelTypeSchema(
        Generator $generator,
        string $module,
        string $domainModel,
        array $domainModelFields
    ): void {
        $modelTypeName = $this->getModelTypeName($domainModel);

        $fileName = $modelTypeName . '.types.yaml';

        $targetPath = $this->namespaceService->getGraphQLModelTypePath($module) . $fileName;

        $this->graphQLSchemaGenerator->generateModelTypeFile(
            $targetPath,
            $modelTypeName
        );

        $yamlContent = $this->namespaceService->getFileContent($targetPath);

        $manipulator = new YamlSourceManipulator($yamlContent);

        $newData = $manipulator->getData();

        $graphQLFields = $this->composeAllSchemaFields($domainModelFields);

        $newData[$modelTypeName]['config'] = [
            'fields' => $graphQLFields,
        ];

        $manipulator->setData($newData);

        $newYaml = $manipulator->getContents();

        $generator->dumpFile($targetPath, $newYaml);
        $generator->writeChanges();
    }

    /**
     * @param EntityField[] $domainModelFields
     */
    private function generateCreateMutationInputTypeSchema(
        Generator $generator,
        string $module,
        string $domainModel,
        array $domainModelFields
    ): void {
        $inputTypeName = $this->getCreateMutationInputTypeName($domainModel);
        $fileName = $inputTypeName . '.types.yaml';

        $targetPath = $this->namespaceService->getGraphQLMutationInputTypesPath($module, $domainModel) . $fileName;

        $this->graphQLSchemaGenerator->generateCreateMutationInputTypeFile(
            $targetPath,
            $inputTypeName
        );

        $yamlContent = $this->namespaceService->getFileContent($targetPath);

        $manipulator = new YamlSourceManipulator($yamlContent);

        $newData = $manipulator->getData();


        $graphQLFields = [];

        foreach ($domainModelFields as $domainModelField) {
            if ('id' === $domainModelField->name) {
                continue;
            }

            $graphQLFields[$domainModelField->name] = [
                'type' => $this->getGraphQLType($domainModelField),
            ];
        }

        $newData[$inputTypeName]['config'] = [
            'fields' => $graphQLFields,
        ];

        $manipulator->setData($newData);

        $newYaml = $manipulator->getContents();

        $generator->dumpFile($targetPath, $newYaml);
        $generator->writeChanges();
    }

    private function generateCreateMutationPayloadTypeSchema(string $module, string $domainModel): void
    {
        $modelTypeName = $this->getModelTypeName($domainModel);

        $payloadTypeName = $this->getCreateMutationPayloadTypeName($domainModel);
        $fileName = $payloadTypeName . '.types.yaml';

        $targetPath = $this->namespaceService->getGraphQLMutationPayloadTypesPath($module, $domainModel) . $fileName;

        $modelField = $this->entityNaming->getSingularNameLower($domainModel);

        $this->graphQLSchemaGenerator->generateCreateMutationPayloadTypeFile(
            $targetPath,
            $payloadTypeName,
            $modelField,
            $modelTypeName
        );
    }

    /**
     * @param EntityField[] $domainModelFields
     */
    private function generateUpdateMutationInputTypeSchema(
        Generator $generator,
        string $module,
        string $domainModel,
        array $domainModelFields
    ): void {
        $inputTypeName = $this->getUpdateMutationInputTypeName($domainModel);
        $fileName = $inputTypeName . '.types.yaml';

        $targetPath = $this->namespaceService->getGraphQLMutationInputTypesPath($module, $domainModel) . $fileName;

        $this->graphQLSchemaGenerator->generateUpdateMutationInputTypeFile(
            $targetPath,
            $inputTypeName
        );

        $yamlContent = $this->namespaceService->getFileContent($targetPath);

        $manipulator = new YamlSourceManipulator($yamlContent);

        $newData = $manipulator->getData();


        $graphQLFields = $this->composeAllSchemaFields($domainModelFields);

        $newData[$inputTypeName]['config'] = [
            'fields' => $graphQLFields,
        ];

        $manipulator->setData($newData);

        $newYaml = $manipulator->getContents();

        $generator->dumpFile($targetPath, $newYaml);
        $generator->writeChanges();
    }

    private function generateUpdateMutationPayloadTypeSchema(
        string $module,
        string $domainModel
    ): void {
        $modelTypeName = $this->getModelTypeName($domainModel);

        $payloadTypeName = $this->getUpdateMutationPayloadTypeName($domainModel);
        $fileName = $payloadTypeName . '.types.yaml';

        $targetPath = $this->namespaceService->getGraphQLMutationPayloadTypesPath($module, $domainModel) . $fileName;

        $modelField = $this->entityNaming->getSingularNameLower($domainModel);

        $this->graphQLSchemaGenerator->generateUpdateMutationPayloadTypeFile(
            $targetPath,
            $payloadTypeName,
            $modelField,
            $modelTypeName
        );
    }

    private function generateDeleteMutationInputTypeSchema(string $module, string $domainModel): void
    {
        $inputTypeName = $this->getDeleteMutationInputTypeName($domainModel);
        $fileName = $inputTypeName . '.types.yaml';

        $targetPath = $this->namespaceService->getGraphQLMutationInputTypesPath($module, $domainModel) . $fileName;

        $this->graphQLSchemaGenerator->generateDeleteMutationInputTypeFile($targetPath, $inputTypeName);
    }

    private function generateDeleteMutationPayloadTypeSchema(string $module, string $domainModel): void
    {
        $payloadTypeName = $this->getDeleteMutationPayloadTypeName($domainModel);
        $fileName = $payloadTypeName . '.types.yaml';

        $targetPath = $this->namespaceService->getGraphQLMutationPayloadTypesPath($module, $domainModel) . $fileName;

        $this->graphQLSchemaGenerator->generateDeleteMutationPayloadTypeFile($targetPath, $payloadTypeName);
    }

    private function registerQueries(Generator $generator, string $domainModel): void
    {
        $typesPath = $this->namespaceService->getGraphQLQueryTypesPath();

        try {
            $yamlContent = $this->namespaceService->getFileContent($typesPath);

            $manipulator = new YamlSourceManipulator($yamlContent);

            $newData = $manipulator->getData();

            $resourceName = $this->entityNaming->getSingularName($domainModel);
            $resourceNameLower = $this->entityNaming->getSingularNameLower($domainModel);
            $collectionName = $this->entityNaming->getPluralName($domainModel);
            $collectionNameLower = $this->entityNaming->getPluralNameLower($domainModel);


            $newData['Query']['config']['fields'][$resourceNameLower] = [
                'type' => sprintf('%s', $resourceName),
                'args' => [
                    'id' => [
                        'type' => 'ID!',
                    ],
                ],
                'resolve' => sprintf('@=query("%s", args.id)', $resourceName),
            ];

            $newData['Query']['config']['fields'][$collectionNameLower] = [
                'type' => sprintf('[%s!]!', $resourceName),
                'resolve' => sprintf('@=query("%s")', $collectionName),
            ];

            $manipulator->setData($newData);

            $newYaml = $manipulator->getContents();

            $generator->dumpFile($typesPath, $newYaml);
            $generator->writeChanges();
        } catch (YamlManipulationFailedException $e) {
        }
    }

    private function registerMutations(Generator $generator, string $domainModel): void
    {
        $typesPath = $this->namespaceService->getGraphQLMutationTypesPath();

        try {
            $yamlContent = $this->namespaceService->getFileContent($typesPath);

            $manipulator = new YamlSourceManipulator($yamlContent);

            $newData = $manipulator->getData();

            $role = 'App\\\\Users\\\\Domain\\\\Enum\\\\UserRoles::ROLE_ADMIN';

            $newData['Mutation']['config']['fields']['create' . $domainModel] = [
                'type' => $this->getCreateMutationPayloadTypeName($domainModel) . '!',
                'access' => '@=hasRole(constant("' .$role . '"))',
                'resolve' => sprintf('@=mutation("Create%s", args.input)', $domainModel),
                'args' => [
                    'input' => [
                        'type' => $this->getCreateMutationInputTypeName($domainModel) . '!',
                    ],
                ],
            ];

            $newData['Mutation']['config']['fields']['update' . $domainModel] = [
                'type' => $this->getUpdateMutationPayloadTypeName($domainModel) . '!',
                'access' => '@=hasRole(constant("' .$role . '"))',
                'resolve' => sprintf('@=mutation("Update%s", args.input)', $domainModel),
                'args' => [
                    'input' => [
                        'type' => $this->getUpdateMutationInputTypeName($domainModel) . '!',
                    ],
                ],
            ];

            $newData['Mutation']['config']['fields']['delete' . $domainModel] = [
                'type' => $this->getDeleteMutationPayloadTypeName($domainModel) . '!',
                'access' => '@=hasRole(constant("' .$role . '"))',
                'resolve' => sprintf('@=mutation("Delete%s", args.input)', $domainModel),
                'args' => [
                    'input' => [
                        'type' => $this->getDeleteMutationInputTypeName($domainModel) . '!',
                    ],
                ],
            ];


            $manipulator->setData($newData);

            $newYaml = $manipulator->getContents();

            $generator->dumpFile($typesPath, $newYaml);
            $generator->writeChanges();
        } catch (YamlManipulationFailedException $e) {
        }
    }

    /**
     * @param EntityField[] $domainModelFields
     */
    private function composeAllSchemaFields(array $domainModelFields): array
    {
        $graphQLFields = [];

        foreach ($domainModelFields as $domainModelField) {
            $graphQLFields[$domainModelField->name] = [
                'type' => $this->getGraphQLType($domainModelField),
            ];
        }

        return $graphQLFields;
    }

    private function getGraphQLType(EntityField $field): string
    {
        if ('id' === $field->name) {
            return 'ID!';
        }

        $type = match ($field->typeHint) {
            'string' => 'String',
            'int' => 'Int',
            'bool' => 'Boolean',
            'float' => 'Float',
            default => 'string',
        };

        if (false === $field->isNullable) {
            $type .= '!';
        }

        return $type;
    }

    private function getModelTypeName(string $domainModel): string
    {
        return $this->entityNaming->getSingularName($domainModel);
    }

    private function getCreateMutationInputTypeName(string $domainModel): string
    {
        return 'Create' . $this->getModelTypeName($domainModel) . 'Input';
    }

    private function getCreateMutationPayloadTypeName(string $domainModel): string
    {
        return 'Create' . $this->getModelTypeName($domainModel) . 'Payload';
    }

    private function getUpdateMutationInputTypeName(string $domainModel): string
    {
        return 'Update' . $this->getModelTypeName($domainModel) . 'Input';
    }

    private function getUpdateMutationPayloadTypeName(string $domainModel): string
    {
        return 'Update' . $this->getModelTypeName($domainModel) . 'Payload';
    }

    private function getDeleteMutationInputTypeName(string $domainModel): string
    {
        return 'Delete' . $this->getModelTypeName($domainModel) . 'Input';
    }

    private function getDeleteMutationPayloadTypeName(string $domainModel): string
    {
        return 'Delete' . $this->getModelTypeName($domainModel) . 'Payload';
    }
}
