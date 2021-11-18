<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Maker;

use Bornfight\MabooMakerBundle\Services\Interactor;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;

class MakeModule extends PlainMaker
{
    private Filesystem $filesystem;
    private string $sourceDirectory;

    public function __construct(
        Interactor $interactor,
        Filesystem $filesystem,
        string $sourceDirectory
    ) {
        parent::__construct($interactor);

        $this->filesystem = $filesystem;
        $this->sourceDirectory = $sourceDirectory;
    }

    public static function getCommandName(): string
    {
        return 'make:maboo-module';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates bounded context (module) folder (if it does not exist yet)';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $this->buildCommand($command)
            ->addModuleArgumentToCommand($command, $inputConfig);
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $this->interactor->collectModuleArguments($input, $io, $command);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $module = $input->getArgument($this->interactor->getModuleArg());
        $modulePath = $this->sourceDirectory . $module;

        if (true === $this->filesystem->exists($modulePath)) {
            $io->comment('<fg=yellow>Directory ' . $modulePath . ' already exists</>');

            return;
        }

        $this->filesystem->mkdir(($modulePath));
        $io->comment('<fg=blue>Directory ' . $modulePath . ' created!</>');

        $this->echoSuccessMessages('Module folder generated!', $io);

    }
}
