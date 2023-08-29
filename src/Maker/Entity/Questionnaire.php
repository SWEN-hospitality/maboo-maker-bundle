<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\Entity;

use Bornfight\MabooMakerBundle\Doctrine\DoctrineHelper;
use InvalidArgumentException;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Doctrine\EntityRelation;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

class Questionnaire
{
    private DoctrineHelper $doctrineHelper;
    private EntityTypes $entityTypes;

    public function __construct(DoctrineHelper $doctrineHelper, EntityTypes $entityTypes)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->entityTypes = $entityTypes;
    }

    public function getEntityClassName(ConsoleStyle $io, string $questionText): string
    {
        $question = $this->createEntityClassQuestion($questionText);
        return $io->askQuestion($question);
    }

    private function createEntityClassQuestion(string $questionText): Question
    {
        $question = new Question($questionText);
        $question->setValidator([Validator::class, 'notBlank']);
        $question->setAutocompleterValues($this->doctrineHelper->getEntitiesForAutocomplete());

        return $question;
    }

    public function getNextField(ConsoleStyle $io, array $existingFields, string $entity): array|EntityRelation|null
    {
        $fieldName = $this->getFieldName($io, $existingFields, $entity);

        if (!$fieldName) {
            return null;
        }

        $fieldType = $this->getFieldType($io, $fieldName);

        if ($this->entityTypes->typeIsRelation($fieldType)) {
            return $this->getRelationDetails($io, $entity, $fieldType, $fieldName);
        }

        return $this->entityTypes->getFieldData($io, $fieldName, $fieldType);
    }

    public function getFieldName(ConsoleStyle $io, array $existingFields, string $entity)
    {
        $question = $this->createFieldNameQuestion($existingFields);
        return $io->askQuestion($question);
    }

    private function createFieldNameQuestion(array $existingFields): Question
    {
        $question = new Question('Enter the property name (or press <return> to stop adding fields');
        $question->setValidator(
            function (?string $name) use ($existingFields) {
                // allow it to be empty
                if (!$name) {
                    return $name;
                }

                if (in_array($name, $existingFields)) {
                    throw new InvalidArgumentException(sprintf('The "%s" property already exists.', $name));
                }

                return Validator::validateDoctrineFieldName($name, $this->doctrineHelper->getRegistry());
            }
        );

        return $question;
    }

    private function getRelationDetails(ConsoleStyle $io, string $entity, string $fieldType, string $fieldName): EntityRelation
    {
        // ask the targetEntity
        $targetEntity = null;
        while (null === $targetEntity) {
            $targetEntity = $this->getTargetEntityClass($io);
        }

        // help the user select the type
        if ('relation' === $fieldType) {
            $fieldType = $this->getRelationType($io, $entity, $targetEntity);
        }

        return $this->getRelationDataBasedOnRelationType($io, $fieldType, $entity, $targetEntity, $fieldName);
    }

    private function getTargetEntityClass(ConsoleStyle $io): ?string
    {
        $question = $this->createEntityClassQuestion('What class should this entity be related to?');

        $answeredEntityClass = $io->askQuestion($question);

        // find the correct class name - but give priority over looking
        // in the Entity namespace versus just checking the full class
        // name to avoid issues with classes like "Directory" that exist
        // in PHP's core.
        if (class_exists($this->doctrineHelper->getEntityNamespace() . '\\' . $answeredEntityClass)) {
            return $this->doctrineHelper->getEntityNamespace() . '\\' . $answeredEntityClass;
        } elseif (class_exists($answeredEntityClass)) {
            return $answeredEntityClass;
        } else {
            $io->error(sprintf('Unknown class "%s"', $answeredEntityClass));
            return null;
        }
    }

    private function getRelationDataBasedOnRelationType(
        ConsoleStyle $io,
        string $relationType,
        string $entity,
        string $targetEntity,
        string $fieldName
    ): EntityRelation {
        switch ($relationType) {
            case EntityRelation::MANY_TO_ONE:
                return $this->getManyToOneRelationData($io, $entity, $targetEntity, $fieldName);
//            case EntityRelation::ONE_TO_MANY:
//                return $this->getOneToManyRelationData($io, $entity, $targetEntity, $fieldName);
//            case EntityRelation::MANY_TO_MANY:
//                return $this->getManyToManyRelationData($io, $entity, $targetEntity, $fieldName);
//            case EntityRelation::ONE_TO_ONE:
//                return $this->getOneToOneRelationData($io, $entity, $targetEntity, $fieldName);
            default:
                throw new InvalidArgumentException('Invalid type: ' . $relationType);
        }
    }

    private function getManyToOneRelationData(
        ConsoleStyle $io,
        string $entity,
        string $targetEntity,
        string $fieldName
    ): EntityRelation {
        $relation = new EntityRelation(EntityRelation::MANY_TO_ONE, $entity, $targetEntity);
        $relation->setOwningProperty($fieldName);
        $relation->setIsNullable($this->getIsRelationFieldNullable(
            $io,
            $relation->getOwningProperty(),
            $relation->getOwningClass()
        ));

        $this->setInverseOfRelation($relation, $io);

        if ($relation->getMapInverseRelation()) {
            $io->comment(sprintf(
                'A new property will also be added to the <comment>%s</comment> class so that you can access the related <comment>%s</comment> objects from it.',
                Str::getShortClassName($relation->getInverseClass()),
                Str::getShortClassName($relation->getOwningClass())
            ));

            $fieldNameQuestion = $this->createRelationFieldNameQuestion(
                $relation->getInverseClass(),
                Str::singularCamelCaseToPluralCamelCase(Str::getShortClassName($relation->getOwningClass()))
            );
            $inverseFieldName = $io->askQuestion($fieldNameQuestion);

            $relation->setInverseProperty($inverseFieldName);

            // orphan removal only applies if the inverse relation is set
            if (!$relation->isNullable()) {
//                $relation->setOrphanRemoval($askOrphanRemoval(
//                    $relation->getOwningClass(),
//                    $relation->getInverseClass()
//                ));
            }
        }

        return $relation;
    }

    private function getOneToManyRelationData(
        ConsoleStyle $io,
        string $entity,
        string $targetEntity,
        string $fieldName
    ): EntityRelation {
        $relation = new EntityRelation(EntityRelation::MANY_TO_ONE, $targetEntity, $entity);

        return $relation;
    }

    private function getManyToManyRelationData(
        ConsoleStyle $io,
        string $entity,
        string $targetEntity,
        string $fieldName
    ): EntityRelation {
        $relation = new EntityRelation(EntityRelation::MANY_TO_MANY, $entity, $targetEntity);

        return $relation;
    }

    private function getOneToOneRelationData(
        ConsoleStyle $io,
        string $entity,
        string $targetEntity,
        string $fieldName
    ): EntityRelation {
        $relation = new EntityRelation(EntityRelation::ONE_TO_ONE, $entity, $targetEntity);

        return $relation;
    }

    private function createRelationFieldNameQuestion(string $targetClass, string $defaultValue): Question
    {
        $question = new Question(
            sprintf('New field name inside %s', Str::getShortClassName($targetClass)),
            $defaultValue
        );
        $question->setValidator(
            function ($name) use ($targetClass) {
                // it's still *possible* to create duplicate properties - by
                // trying to generate the same property 2 times during the
                // same make:entity run. property_exists() only knows about
                // properties that *originally* existed on this class.
                if (property_exists($targetClass, $name)) {
                    throw new InvalidArgumentException(sprintf('The "%s" class already has a "%s" property.',
                        $targetClass, $name));
                }

                return Validator::validateDoctrineFieldName($name, $this->doctrineHelper->getRegistry());
            }
        );

        return $question;
    }

    private function setInverseOfRelation(EntityRelation $relation, ConsoleStyle $io)
    {
//        if ($this->isClassInVendor($relation->getInverseClass())) {
//            $relation->setMapInverseRelation(false);
//
//            return;
//        }

        // recommend an inverse side, except for OneToOne, where it's inefficient
        $recommendMappingInverse = EntityRelation::ONE_TO_ONE !== $relation->getType();

        $getterMethodName = 'get' . Str::asCamelCase(Str::getShortClassName($relation->getOwningClass()));
        if (EntityRelation::ONE_TO_ONE !== $relation->getType()) {
            // pluralize!
            $getterMethodName = Str::singularCamelCaseToPluralCamelCase($getterMethodName);
        }

        $mapInverse = $io->confirm(
            sprintf(
                'Do you want to add a new property to <comment>%s</comment> so that you can access/update <comment>%s</comment> objects from it - e.g. <comment>$%s->%s()</comment>?',
                Str::getShortClassName($relation->getInverseClass()),
                Str::getShortClassName($relation->getOwningClass()),
                Str::asLowerCamelCase(Str::getShortClassName($relation->getInverseClass())),
                $getterMethodName
            ),
            $recommendMappingInverse
        );
        $relation->setMapInverseRelation($mapInverse);
    }

    private function getRelationType(ConsoleStyle $io, string $entity, string $targetEntity): string
    {
        $this->printAvailableRelationTypes($io, $entity, $targetEntity);

        $question = $this->createRelationTypeQuestion();

        return $io->askQuestion($question);
    }

    private function createRelationTypeQuestion(): Question
    {
        $question = new Question(sprintf(
            'Relation type? [%s]',
            implode(', ', $this->entityTypes->getAllValidRelationTypes())
        ));
        $question->setAutocompleterValues($this->entityTypes->getAllValidRelationTypes());
        $question->setValidator(function ($type) {
            if (!in_array($type, $this->entityTypes->getAllValidRelationTypes())) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid type: use one of: %s',
                    implode(', ', $this->entityTypes->getAllValidRelationTypes())
                ));
            }

            if (EntityRelation::MANY_TO_ONE !== $type) {
                throw new InvalidArgumentException('Only Many-to-One relation is supported for now');
            }

            return $type;
        });

        return $question;
    }

    private function getIsRelationFieldNullable(ConsoleStyle $io, string $propertyName, string $targetEntity)
    {
        return $io->confirm(sprintf(
            'Is the <comment>%s</comment>.<comment>%s</comment> property allowed to be null (nullable)?',
            Str::getShortClassName($targetEntity),
            $propertyName
        ));
    }

    public function getFieldType(ConsoleStyle $io, string $fieldName)
    {
        $fieldType = null;

        $allValidTypes = $this->entityTypes->getAllValidTypes();
        $defaultType = $this->entityTypes->determineDefaultTypeBasedOnFieldName($fieldName);

        while (null === $fieldType) {
            $question = $this->createFieldTypeQuestion($defaultType, $allValidTypes);
            $fieldType = $io->askQuestion($question);

            if ('?' === $fieldType) {
                $this->printAvailableTypes($io);
                $io->writeln('');

                $fieldType = null;
            } elseif (!in_array($fieldType, $allValidTypes)) {
                $this->printAvailableTypes($io);
                $io->error(sprintf('Invalid type "%s".', $fieldType));
                $io->writeln('');

                $fieldType = null;
            }
        }

        return $fieldType;
    }

    private function createFieldTypeQuestion(string $defaultType, array $allValidTypes): Question
    {
        $question = new Question('Field type (enter <comment>?</comment> to see all types)', $defaultType);
        $question->setAutocompleterValues($allValidTypes);

        return $question;
    }

    private function printAvailableTypes(ConsoleStyle $io)
    {
        $allTypes = $this->entityTypes->getAllTypes();
        $typesTable = $this->entityTypes->getTypesTable();

        $printSection = function (array $sectionTypes) use ($io, &$allTypes) {
            foreach ($sectionTypes as $mainType => $subTypes) {
                unset($allTypes[$mainType]);
                $line = sprintf('  * <comment>%s</comment>', $mainType);

                if (is_string($subTypes) && $subTypes) {
                    $line .= sprintf(' (%s)', $subTypes);
                } elseif (is_array($subTypes) && !empty($subTypes)) {
                    $line .= sprintf(' (or %s)', implode(', ', array_map(function ($subType) {
                        return sprintf('<comment>%s</comment>', $subType);
                    }, $subTypes)));

                    foreach ($subTypes as $subType) {
                        unset($allTypes[$subType]);
                    }
                }

                $io->writeln($line);
            }

            $io->writeln('');
        };

        $io->writeln('<info>Main types</info>');
        $printSection($typesTable['main']);

        $io->writeln('<info>Relationships / Associations</info>');
        $printSection($typesTable['relation']);

        $io->writeln('<info>Array/Object Types</info>');
        $printSection($typesTable['array_object']);

        $io->writeln('<info>Date/Time Types</info>');
        $printSection($typesTable['date_time']);

        $io->writeln('<info>Other Types</info>');
        // empty the values
        $allTypes = array_map(function () {
            return [];
        }, $allTypes);
        $printSection($allTypes);
    }

    private function printAvailableRelationTypes(ConsoleStyle $io, string $entityClass, string $targetEntityClass): void
    {
        $io->writeln('What type of relationship is this?');

        $originalEntityShort = Str::getShortClassName($entityClass);
        $targetEntityShort = Str::getShortClassName($targetEntityClass);
        $rows = [];
        $rows[] = [
            EntityRelation::MANY_TO_ONE,
            sprintf("Each <comment>%s</comment> relates to (has) <info>one</info> <comment>%s</comment>.\nEach <comment>%s</comment> can relate to (can have) <info>many</info> <comment>%s</comment> objects",
                $originalEntityShort, $targetEntityShort, $targetEntityShort, $originalEntityShort),
        ];
        $rows[] = ['', ''];
        $rows[] = [
            EntityRelation::ONE_TO_MANY,
            sprintf("Each <comment>%s</comment> can relate to (can have) <info>many</info> <comment>%s</comment> objects.\nEach <comment>%s</comment> relates to (has) <info>one</info> <comment>%s</comment>",
                $originalEntityShort, $targetEntityShort, $targetEntityShort, $originalEntityShort),
        ];
        $rows[] = ['', ''];
        $rows[] = [
            EntityRelation::MANY_TO_MANY,
            sprintf("Each <comment>%s</comment> can relate to (can have) <info>many</info> <comment>%s</comment> objects.\nEach <comment>%s</comment> can also relate to (can also have) <info>many</info> <comment>%s</comment> objects",
                $originalEntityShort, $targetEntityShort, $targetEntityShort, $originalEntityShort),
        ];
        $rows[] = ['', ''];
        $rows[] = [
            EntityRelation::ONE_TO_ONE,
            sprintf("Each <comment>%s</comment> relates to (has) exactly <info>one</info> <comment>%s</comment>.\nEach <comment>%s</comment> also relates to (has) exactly <info>one</info> <comment>%s</comment>.",
                $originalEntityShort, $targetEntityShort, $targetEntityShort, $originalEntityShort),
        ];

        $io->table([
            'Type',
            'Description',
        ], $rows);
    }
}
