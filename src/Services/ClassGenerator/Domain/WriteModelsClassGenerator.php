<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Domain;

use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class WriteModelsClassGenerator
{
    public function __construct(private Generator $generator)
    {
    }

    public function generateCreateWriteModelClass(ClassNameDetails $createWriteModelClassDetails): string
    {
        return $this->generator->generateClass(
            $createWriteModelClassDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/domain/CreateWriteModel.tpl.php',
            []
        );
    }

    public function generateUpdateWriteModelClass(ClassNameDetails $updateWriteModelClassDetails): string
    {
        return $this->generator->generateClass(
            $updateWriteModelClassDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/domain/UpdateWriteModel.tpl.php',
            []
        );
    }
}
