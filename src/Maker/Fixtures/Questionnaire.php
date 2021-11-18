<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\Fixtures;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

class Questionnaire
{
    public function getFixturesClassName(ConsoleStyle $io, string $questionText, ?string $domainModel): string
    {
        $question = $this->createFixturesClassQuestion($questionText, $domainModel);
        return $io->askQuestion($question);
    }

    private function createFixturesClassQuestion(string $questionText, ?string $domainModel): Question
    {
        $defaultClassName = null;
        if (null !== $domainModel) {
            $defaultClassName = $domainModel . 'Fixtures';
        }

        $question = new Question($questionText, $defaultClassName);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }
}
