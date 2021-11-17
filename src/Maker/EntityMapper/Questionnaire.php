<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\EntityMapper;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

class Questionnaire
{
    public function getEntityMapperClassName(ConsoleStyle $io, string $questionText, ?string $entity): string
    {
        $question = $this->createEntityMapperClassQuestion($questionText, $entity);
        return $io->askQuestion($question);
    }

    private function createEntityMapperClassQuestion(string $questionText, ?string $entity): Question
    {
        $defaultClassName = null;
        if (null !== $entity) {
            $defaultClassName = $entity . 'Mapper';
        }

        $question = new Question($questionText, $defaultClassName);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }
}
