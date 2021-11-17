<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\Validator;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

class Questionnaire
{
    public function getValidatorClassName(ConsoleStyle $io, string $questionText, ?string $domainModel): string
    {
        $question = $this->createValidatorClassQuestion($questionText, $domainModel);
        return $io->askQuestion($question);
    }

    public function getSpecificationInterfaceName(ConsoleStyle $io, string $questionText, ?string $model): string
    {
        $question = $this->createSpecificationInterfaceQuestion($questionText, $model);
        return $io->askQuestion($question);
    }

    public function getSpecificationClassName(ConsoleStyle $io, string $questionText, ?string $specificationInterface): string
    {
        $question = $this->createSpecificationClassQuestion($questionText, $specificationInterface);
        return $io->askQuestion($question);
    }

    private function createValidatorClassQuestion(string $questionText, ?string $domainModel): Question
    {
        $defaultClassName = null;
        if (null !== $domainModel) {
            $defaultClassName = $domainModel . 'Validator';
        }

        $question = new Question($questionText, $defaultClassName);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }

    private function createSpecificationInterfaceQuestion(string $questionText, ?string $model): Question
    {
        $defaultInterfaceName = null;
        if (null !== $model) {
            $defaultInterfaceName = 'IsExisting' . $model . 'Specification';
        }

        $question = new Question($questionText, $defaultInterfaceName);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }

    private function createSpecificationClassQuestion(string $questionText, ?string $specificationInterface): Question
    {
        $defaultClassName = null;
        if (null !== $specificationInterface) {
            $defaultClassName = 'Doctrine' . $specificationInterface;
        }

        $question = new Question($questionText, $defaultClassName);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }
}
