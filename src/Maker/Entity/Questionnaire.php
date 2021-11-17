<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\Entity;

use Bornfight\MabooMakerBundle\Doctrine\DoctrineHelper;
use InvalidArgumentException;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
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

    public function getNextField(ConsoleStyle $io, array $existingFields, string $entity): ?array
    {
        $fieldName = $this->getFieldName($io, $existingFields, $entity);

        if (!$fieldName) {
            return null;
        }

        $fieldType = $this->getFieldType($io, $fieldName);

        if ($this->entityTypes->typeIsRelation($fieldType)) {
            throw new RuntimeCommandException('Not yet supported');
//            return $this->askRelationDetails($io, $entityClass, $type, $fieldName);
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
}
