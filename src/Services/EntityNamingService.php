<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

class EntityNamingService
{
    private Inflector $inflector;
    
    public function __construct()
    {
        $this->inflector = InflectorFactory::create()->build();
    }

    public function getSingularName(string $name): string
    {
        return ucfirst($this->inflector->singularize($name));
    }

    public function getPluralName(string $name): string
    {
        return ucfirst($this->inflector->pluralize($name));
    }

    public function getSingularNameLower(string $name): string
    {
        return lcfirst($this->getSingularName($name));
    }

    public function getPluralNameLower(string $name): string
    {
        return lcfirst($this->getPluralName($name));
    }
}
