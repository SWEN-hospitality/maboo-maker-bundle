<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Util;

class MakerSelection
{
    public const DOMAIN_MODEL = 'domain_model';
    public const WRITE_MODELS = 'write_models';
    public const MAPPER = 'mapper';
    public const REPOSITORY = 'repository';
    public const VALIDATOR = 'validator';
    public const MANAGER = 'manager';
    public const RESOLVER = 'resolver';
    public const MUTATION = 'mutation';
    public const FIXTURES = 'fixtures';

    private array $selectedComponents = [];

    public function getSelectedComponents(): array
    {
        return $this->selectedComponents;
    }

    public function setSelectedComponents(array $selectedComponents): void
    {
        $this->selectedComponents = $selectedComponents;
    }

    public function shouldCreateDomainModel(): bool
    {
        return in_array(self::DOMAIN_MODEL, $this->selectedComponents);
    }

    public function shouldCreateWriteModels(): bool
    {
        return in_array(self::WRITE_MODELS, $this->selectedComponents);
    }

    public function shouldCreateMapper(): bool
    {
        return in_array(self::MAPPER, $this->selectedComponents);
    }

    public function shouldCreateRepository(): bool
    {
        return in_array(self::REPOSITORY, $this->selectedComponents);
    }

    public function shouldCreateValidator(): bool
    {
        return in_array(self::VALIDATOR, $this->selectedComponents);
    }

    public function shouldCreateManager(): bool
    {
        return in_array(self::MANAGER, $this->selectedComponents);
    }

    public function shouldCreateResolver(): bool
    {
        return in_array(self::RESOLVER, $this->selectedComponents);
    }

    public function shouldCreateMutation(): bool
    {
        return in_array(self::MUTATION, $this->selectedComponents);
    }
}
