<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\Mutation;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

class Questionnaire
{
    public function getMutationClassName(ConsoleStyle $io, string $questionText, ?string $domainModel): string
    {
        $question = $this->createMutationClassQuestion($questionText, $domainModel);
        return $io->askQuestion($question);
    }

    private function createMutationClassQuestion(string $questionText, ?string $domainModel): Question
    {
        $defaultClassName = null;
        if (null !== $domainModel) {
            $defaultClassName = $domainModel . 'Mutation';
        }

        $question = new Question($questionText, $defaultClassName);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }
}
