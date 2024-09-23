<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services;

use Bornfight\MabooMakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Component\Finder\Finder;

class NamespaceService
{
    public function __construct(
        private DoctrineHelper $doctrineHelper,
        private string $entityNamespace,
        private string $domainModelNamespaceTemplate,
        private string $writeModelsNamespaceTemplate,
        private string $entityMapperNamespaceTemplate,
        private string $repositoryInterfaceNamespaceTemplate,
        private string $repositoryClassNamespaceTemplate,
        private string $validatorNamespaceTemplate,
        private string $specificationInterfaceNamespaceTemplate,
        private string $specificationClassNamespaceTemplate,
        private string $managerNamespaceTemplate,
        private string $resolverNamespaceTemplate,
        private string $mutationNamespaceTemplate,
        private string $fixturesNamespaceTemplate,
        private string $gqlQueryTypesPath,
        private string $gqlMutationTypesPath,
        private string $gqlModelTypePathTemplate,
        private string $gqlMutationInputTypesPathTemplate,
        private string $gqlMutationPayloadTypesPathTemplate,
        private FileManager $fileManager,
        private string $projectSourceDirectory
    ) {
    }

    public function getEntityNamespace(): string
    {
        return $this->entityNamespace;
    }

    public function getDoctrineEntityNamespace(): string
    {
        return $this->doctrineHelper->getEntityNamespace();
    }

    public function getDomainModelNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->domainModelNamespaceTemplate);
    }

    public function getWriteModelsNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->writeModelsNamespaceTemplate);
    }

    public function getRepositoryInterfaceNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->repositoryInterfaceNamespaceTemplate);
    }

    public function getRepositoryClassNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->repositoryClassNamespaceTemplate);
    }

    public function getEntityMapperNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->entityMapperNamespaceTemplate);
    }

    public function getManagerNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->managerNamespaceTemplate);
    }

    public function getValidatorNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->validatorNamespaceTemplate);
    }

    public function getSpecificationInterfaceNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->specificationInterfaceNamespaceTemplate);
    }

    public function getSpecificationClassNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->specificationClassNamespaceTemplate);
    }

    public function getResolverNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->resolverNamespaceTemplate);
    }

    public function getMutationNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->mutationNamespaceTemplate);
    }

    public function getFixturesNamespace(string $module): string
    {
        return str_replace('_module_', $module, $this->fixturesNamespaceTemplate);
    }

    public function getGraphQLQueryTypesPath(): string
    {
        return $this->gqlQueryTypesPath;
    }

    public function getGraphQLMutationTypesPath(): string
    {
        return $this->gqlMutationTypesPath;
    }

    public function getGraphQLModelTypePath(string $module): string
    {
        return str_replace('_module_', $module, $this->gqlModelTypePathTemplate);
    }

    public function getGraphQLMutationInputTypesPath(string $module, string $model): string
    {
        return str_replace(['_module_', '_model_'], [$module, $model], $this->gqlMutationInputTypesPathTemplate);
    }

    public function getGraphQLMutationPayloadTypesPath(string $module, string $model): string
    {
        return str_replace(['_module_', '_model_'], [$module, $model], $this->gqlMutationPayloadTypesPathTemplate);
    }

    public function createFinder(): Finder
    {
        return $this->fileManager->createFinder($this->projectSourceDirectory);
    }

    public function getFileContent(string $path): string
    {
        return $this->fileManager->getFileContents($path);
    }
}
