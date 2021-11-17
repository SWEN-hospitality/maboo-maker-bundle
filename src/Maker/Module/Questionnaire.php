<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\Module;

use Bornfight\MabooMakerBundle\Services\NamespaceService;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

class Questionnaire
{
    private NamespaceService $namespaceService;

    public function __construct(NamespaceService $namespaceService)
    {
        $this->namespaceService = $namespaceService;
    }

    public function getModule(ConsoleStyle $io, string $questionText): string
    {
        $question = $this->createModuleQuestion($questionText);
        $module = $this->sanitizeName($io->askQuestion($question));

        $io->writeln(sprintf('You have just selected module: <bg=yellow;fg=black> %s </>', $module));

        return $module;
    }

    public function sanitizeName(string $module): string
    {
        return Str::asClassName($module);
    }

    private function createModuleQuestion(string $questionText): Question
    {
        $question = new Question($questionText);
        $question->setValidator([Validator::class, 'notBlank']);
        $question->setAutocompleterValues($this->getModuleChoices());

        return $question;
    }

    private function getModuleChoices(): array
    {
        $choices = [];

        $directories = $this->namespaceService->createFinder()
            ->directories()
            ->depth(0)
            ->exclude(['Repository', 'Shared'])
            ->sortByName();

        foreach ($directories as $dir) {
            $choices[] = $dir->getRelativePathname();
        }

        return $choices;
    }
}
