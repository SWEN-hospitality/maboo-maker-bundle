<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\WriteModels;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

class Questionnaire
{
    public function getCreateModelClassName(ConsoleStyle $io, string $questionText, ?string $model): string
    {
        $question = $this->createCreateModelClassQuestion($questionText, $model);
        return $io->askQuestion($question);
    }

    public function getUpdateModelClassName(ConsoleStyle $io, string $questionText, ?string $model): string
    {
        $question = $this->createUpdateModelClassQuestion($questionText, $model);
        return $io->askQuestion($question);
    }

    private function createCreateModelClassQuestion(string $questionText, ?string $model): Question
    {
        $defaultClassName = null;
        if (null !== $model) {
            $defaultClassName = 'Create' . $model;
        }

        $question = new Question($questionText, $defaultClassName);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }

    private function createUpdateModelClassQuestion(string $questionText, ?string $model): Question
    {
        $defaultClassName = null;
        if (null !== $model) {
            $defaultClassName = 'Update' . $model;
        }

        $question = new Question($questionText, $defaultClassName);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }
}
