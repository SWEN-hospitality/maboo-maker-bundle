<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Doctrine;

use Bornfight\MabooMakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class EntityClassGenerator
{
    private Generator $generator;
    private DoctrineHelper $doctrineHelper;

    public function __construct(Generator $generator, DoctrineHelper $doctrineHelper)
    {
        $this->generator = $generator;
        $this->doctrineHelper = $doctrineHelper;
    }

    public function generateEntityClass(
        ClassNameDetails $entityClassDetails
    ): string {
        $tableName = $this->doctrineHelper->getPotentialTableName($entityClassDetails->getFullName());

        $supportsAttributes = $this->doctrineHelper->isDoctrineSupportingAttributes();

        return $this->generator->generateClass(
            $entityClassDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/doctrine/Entity.tpl.php',
            [
                'should_escape_table_name' => $this->doctrineHelper->isKeyword($tableName),
                'table_name' => $tableName,
                'doctrine_use_attributes' => $supportsAttributes
                    && $this->doctrineHelper->doesClassUsesAttributes($entityClassDetails->getFullName()),
            ]
        );
    }
}
