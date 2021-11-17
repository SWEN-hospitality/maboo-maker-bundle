<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\Resolver;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

class Questionnaire
{
    public function getResolverClassName(ConsoleStyle $io, string $questionText, ?string $domainModel): string
    {
        $question = $this->createResolverClassQuestion($questionText, $domainModel);
        return $io->askQuestion($question);
    }

    private function createResolverClassQuestion(string $questionText, ?string $domainModel): Question
    {
        $defaultClassName = null;
        if (null !== $domainModel) {
            $defaultClassName = $domainModel . 'Resolver';
        }

        $question = new Question($questionText, $defaultClassName);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }
}
