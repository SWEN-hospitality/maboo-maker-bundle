<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\DomainModel;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

class Questionnaire
{
    public function getModelClassName(ConsoleStyle $io, string $questionText, ?string $entity): string
    {
        $question = $this->createDomainModelClassQuestion($questionText, $entity);
        return $io->askQuestion($question);
    }

    private function createDomainModelClassQuestion(string $questionText, ?string $entity): Question
    {
        $question = new Question($questionText, $entity);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }
}
