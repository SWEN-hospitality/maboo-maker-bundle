<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Infrastructure;

use Symfony\Bundle\MakerBundle\Generator;

class GraphQLSchemaGenerator
{
    public function __construct(private Generator $generator)
    {
    }

    public function generateModelTypeFile(
        string $targetPath,
        string $modelTypeName
    ): void {
        $this->generator->generateFile(
            $targetPath,
            __DIR__ . '/../../../Resources/skeleton/infrastructure/graphql/modelType.yaml.tpl.php',
            [
                'model_type_name' => $modelTypeName,
            ]
        );
        $this->generator->writeChanges();
    }

    public function generateCreateMutationInputTypeFile(
        string $targetPath,
        string $inputTypeName
    ): void {
        $this->generator->generateFile(
            $targetPath,
            __DIR__ . '/../../../Resources/skeleton/infrastructure/graphql/createMutationInput.yaml.tpl.php',
            [
                'input_type_name' => $inputTypeName,
            ]
        );
        $this->generator->writeChanges();
    }

    public function generateCreateMutationPayloadTypeFile(
        string $targetPath,
        string $payloadTypeName,
        string $model,
        string $modelType
    ): void {
        $this->generator->generateFile(
            $targetPath,
            __DIR__ . '/../../../Resources/skeleton/infrastructure/graphql/createMutationPayload.yaml.tpl.php',
            [
                'payload_type_name' => $payloadTypeName,
                'model' => $model,
                'model_type' => $modelType,
            ]
        );
        $this->generator->writeChanges();
    }

    public function generateUpdateMutationInputTypeFile(string $targetPath, string $inputTypeName): void
    {
        $this->generator->generateFile(
            $targetPath,
            __DIR__ . '/../../../Resources/skeleton/infrastructure/graphql/updateMutationInput.yaml.tpl.php',
            [
                'input_type_name' => $inputTypeName,
            ]
        );
        $this->generator->writeChanges();
    }

    public function generateUpdateMutationPayloadTypeFile(
        string $targetPath,
        string $payloadTypeName,
        string $model,
        string $modelType
    ): void {
        $this->generator->generateFile(
            $targetPath,
            __DIR__ . '/../../../Resources/skeleton/infrastructure/graphql/updateMutationPayload.yaml.tpl.php',
            [
                'payload_type_name' => $payloadTypeName,
                'model' => $model,
                'model_type' => $modelType,
            ]
        );
        $this->generator->writeChanges();
    }

    public function generateDeleteMutationInputTypeFile(
        string $targetPath,
        string $inputTypeName
    ): void {
        $this->generator->generateFile(
            $targetPath,
            __DIR__ . '/../../../Resources/skeleton/infrastructure/graphql/deleteMutationInput.yaml.tpl.php',
            [
                'input_type_name' => $inputTypeName,
            ]
        );
        $this->generator->writeChanges();
    }

    public function generateDeleteMutationPayloadTypeFile(
        string $targetPath,
        string $payloadTypeName
    ): void {
        $this->generator->generateFile(
            $targetPath,
            __DIR__ . '/../../../Resources/skeleton/infrastructure/graphql/deleteMutationPayload.yaml.tpl.php',
            [
                'payload_type_name' => $payloadTypeName,
            ]
        );
        $this->generator->writeChanges();
    }
}
