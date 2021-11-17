<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker\Scaffold;

use Bornfight\MabooMakerBundle\Util\MakerSelection;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;

class Questionnaire
{
    public function getComponentsSelection(ConsoleStyle $io, MakerSelection $makerSelection)
    {
        $questionText =
            'What else, apart from the entity class, you want this tool to generate for you?' . PHP_EOL .
            ' Multiple components are divided by commas. Example: 0,1,2,6' . PHP_EOL .
            ' By default, all components are selected.' . PHP_EOL;
        $choices = $this->getAvailableComponentOptions();

        $question = new ChoiceQuestion(
            $questionText,
            $choices,
            join(',', array_keys($choices))
        );
        $question->setMultiselect(true);

        $selectedComponents = $io->askQuestion($question);

        $io->writeln('You have just selected: ' . implode(', ', $selectedComponents));

        $makerSelection->setSelectedComponents($selectedComponents);

        return $selectedComponents;
    }

    private function getAvailableComponentOptions(): array
    {
        return [
            MakerSelection::DOMAIN_MODEL,
            MakerSelection::WRITE_MODELS,
            MakerSelection::MAPPER,
            MakerSelection::REPOSITORY,
            MakerSelection::VALIDATOR,
            MakerSelection::MANAGER,
            MakerSelection::RESOLVER,
            MakerSelection::MUTATION,
            MakerSelection::FIXTURES,
        ];
    }
}
