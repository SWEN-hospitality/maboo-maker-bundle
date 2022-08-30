<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassManipulator;

use Symfony\Bundle\MakerBundle\FileManager;

class ClassManipulatorManager
{
    private FileManager $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function createEntityManipulator(
        string $fullClassName,
        bool $useAnnotations = false,
        bool $useAttributes = true,
        bool $fluentMutators = true
    ): EntityManipulator {
        return new EntityManipulator(
            $this->getFileContent($fullClassName),
            $useAnnotations,
            $useAttributes,
            $fluentMutators
        );
    }

    /**
     * @return EntityField[]
     */
    public function getEntityFields(string $fileContent): array
    {
        return $this->createEntityManipulator($fileContent)->getAllFields();
    }

    public function createDomainModelManipulator(string $fullClassName): DomainModelManipulator
    {
        return new DomainModelManipulator($this->getFileContent($fullClassName), false);
    }

    /**
     * @return EntityField[]
     */
    public function getDomainModelFields(string $fileContent): array
    {
        return $this->createDomainModelManipulator($fileContent)->getAllFields();
    }

    public function createWriteModelManipulator(string $fullClassName): WriteModelManipulator
    {
        return new WriteModelManipulator($this->getFileContent($fullClassName), false);
    }

    public function createGenericClassManipulator(string $fullClassName): GenericClassManipulator
    {
        return new GenericClassManipulator($this->getFileContent($fullClassName));
    }

    public function dumpFile(string $filename, string $content): void
    {
        $this->fileManager->dumpFile($filename, $content);
    }

    private function getFileContent(string $className): string
    {
        $classPath = $this->fileManager->getRelativePathForFutureClass($className);

        return $this->fileManager->getFileContents($classPath);
    }
}
