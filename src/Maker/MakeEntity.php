<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Doctrine\DoctrineHelper;
use Bornfight\MabooMakerBundle\Maker\Entity\EntityTypes;
use Bornfight\MabooMakerBundle\Maker\Entity\Questionnaire;
use Bornfight\MabooMakerBundle\Services\ClassGenerator\Doctrine\EntityClassGenerator;
use Bornfight\MabooMakerBundle\Services\ClassManipulator\ClassManipulatorManager;
use Bornfight\MabooMakerBundle\Services\Interactor;
use Bornfight\MabooMakerBundle\Services\NamespaceService;
use Bornfight\MabooMakerBundle\Util\ClassProperties;
use Exception;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputAwareMakerInterface;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class MakeEntity extends PlainMaker implements InputAwareMakerInterface
{
    use ClassProperties;

    private Questionnaire $questionnaire;
    private DoctrineHelper $doctrineHelper;
    private EntityClassGenerator $entityClassGenerator;
    private NamespaceService $namespaceService;
    private ClassManipulatorManager $manipulatorManager;
    private EntityTypes $entityTypes;

    public function __construct(
        Interactor $interactor,
        Questionnaire $questionnaire,
        DoctrineHelper $doctrineHelper,
        EntityClassGenerator $entityClassGenerator,
        NamespaceService $namespaceService,
        ClassManipulatorManager $manipulatorManager,
        EntityTypes $entityTypes
    ) {
        parent::__construct($interactor);

        $this->questionnaire = $questionnaire;
        $this->doctrineHelper = $doctrineHelper;
        $this->entityClassGenerator = $entityClassGenerator;
        $this->namespaceService = $namespaceService;
        $this->manipulatorManager = $manipulatorManager;
        $this->entityTypes = $entityTypes;
    }

    public static function getCommandName(): string
    {
        return 'make:maboo-entity';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates or updates a Doctrine entity class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $this->buildCommand($command)
            ->addEntityArgumentToCommand($command, $inputConfig);
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $this->interactor->collectEntityArguments($input, $io, $command);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $entityClassDetails = $generator->createClassNameDetails(
            $input->getArgument($this->interactor->getEntityArg()),
            $this->namespaceService->getEntityNamespace()
        );

        $classFullName = $entityClassDetails->getFullName();

        $classExists = class_exists($classFullName);

        if (false === $classExists) {
            $entityPath = $this->entityClassGenerator->generateEntityClass(
                $entityClassDetails
            );

            $generator->writeChanges();
        } else {
            $entityPath = $this->getPathOfClass($classFullName);
        }

        if (false === $this->doesEntityUseAnnotationMapping($classFullName)
            && false === $this->doesEntityUseAttributeMapping($classFullName)
        ) {
            throw new RuntimeCommandException(
                sprintf(
                    'Only annotation or attribute mapping is supported by make:maboo-entity, but the %s class uses a different format. If you would like this command to generate the properties & getter/setter methods, add your mapping configuration, and then re-run this command with the <info>--regenerate</info> flag.',
                    $classFullName
                )
            );
        }

        if (true === $classExists) {
            $this->echoInfoMessages('Your entity already exists! So let\'s add some new fields!', $io);
        } else {
            $this->echoSuccessMessages('Entity generated! Now let\'s add some fields!', $io);
            $this->echoInfoMessages([
                'You can always add more fields later manually or by re-running this command.',
                'Identifier field will be added by default (UUID [string])',
            ], $io);
        }

        $currentFields = $this->getPropertyNames($classFullName);

        $useAttributes = $this->doctrineHelper->doesClassUsesAttributes($classFullName) && $this->doctrineHelper->isDoctrineSupportingAttributes();
        $useAnnotations = $this->doctrineHelper->isClassAnnotated($classFullName);
        $entityManipulator = $this->manipulatorManager->createEntityManipulator(
            $classFullName,
            $useAnnotations,
            $useAttributes,
            true
        );

        while (true) {
            $newField = $this->questionnaire->getNextField(
                $io,
                $currentFields,
                $entityClassDetails->getFullName()
            );

            if (null === $newField) {
                break;
            }

            $fileManagerOperations = [];
            $fileManagerOperations[$entityPath] = $entityManipulator;

            if (is_array($newField)) {
                $annotationOptions = $newField;
                unset($annotationOptions['fieldName']);
                $entityManipulator->addField($newField['fieldName'], $annotationOptions);

                $currentFields[] = $newField['fieldName'];
            } else {
                throw new Exception('Invalid value');
            }

            foreach ($fileManagerOperations as $path => $manipulatorOrMessage) {
                if (is_string($manipulatorOrMessage)) {
                    $io->comment($manipulatorOrMessage);
                } else {
                    $this->manipulatorManager->dumpFile($path, $manipulatorOrMessage->getSourceCode());
                }
            }
        }

        $this->echoSuccessMessages('Entity generated!', $io);

        $io->text([
            'Next: When you\'re ready, create a migration with <info>php bin/console make:migration</info>',
            '',
        ]);
    }

    private function doesEntityUseAnnotationMapping(string $className): bool
    {
        if (!class_exists($className)) {
            $otherClassMetadata = $this->doctrineHelper->getMetadata(Str::getNamespace($className) . '\\', true);

            // if we have no metadata, we should assume this is the first class being mapped
            if (empty($otherClassMetadata)) {
                return false;
            }

            $className = reset($otherClassMetadata)->getName();
        }

        return $this->doctrineHelper->isClassAnnotated($className);
    }

    private function doesEntityUseAttributeMapping(string $className): bool
    {
        if (PHP_VERSION < 80000) {
            return false;
        }

        if (!class_exists($className)) {
            $otherClassMetadata = $this->doctrineHelper->getMetadata(Str::getNamespace($className) . '\\', true);

            // if we have no metadata, we should assume this is the first class being mapped
            if (empty($otherClassMetadata)) {
                return false;
            }

            $className = reset($otherClassMetadata)->getName();
        }

        return $this->doctrineHelper->doesClassUsesAttributes($className);
    }
}
