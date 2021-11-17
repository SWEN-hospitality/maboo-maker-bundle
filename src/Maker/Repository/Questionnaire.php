<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\Repository;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

class Questionnaire
{
    public function getRepositoryInterfaceName(ConsoleStyle $io, string $questionText, ?string $model): string
    {
        $question = $this->createRepositoryInterfaceQuestion($questionText, $model);
        return $io->askQuestion($question);
    }

    public function getRepositoryClassName(ConsoleStyle $io, string $questionText, ?string $repositoryInterface): string
    {
        $question = $this->createRepositoryClassQuestion($questionText, $repositoryInterface);
        return $io->askQuestion($question);
    }

    private function createRepositoryInterfaceQuestion(string $questionText, ?string $model): Question
    {
        $defaultInterfaceName = null;
        if (null !== $model) {
            $defaultInterfaceName = $model . 'Repository';
        }

        $question = new Question($questionText, $defaultInterfaceName);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }

    private function createRepositoryClassQuestion(string $questionText, ?string $repositoryInterface): Question
    {
        $defaultClassName = null;
        if (null !== $repositoryInterface) {
            $defaultClassName = 'Doctrine' . $repositoryInterface;
        }

        $question = new Question($questionText, $defaultClassName);
        $question->setValidator([Validator::class, 'notBlank']);

        return $question;
    }
}
