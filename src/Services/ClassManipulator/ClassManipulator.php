<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassManipulator;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;

abstract class ClassManipulator extends ClassSourceManipulator
{
    /**
     * @return EntityField[]
     */
    public function getAllFields(): array
    {
        return array_map(
            function (Property $property) {
                return new EntityField($property);
            },
            array_filter(
                $this->getClassNode()->stmts,
                fn ($stmt) => $stmt instanceof Property
            )
        );
    }

    protected function addClassField(
        string $name,
        $type,
        bool $isNullable,
        array $comments = [],
        array $attributes = []
    ) {
        $defaultValue = null;
        if ('array' === $type) {
            $defaultValue = new Array_([], ['kind' => Array_::KIND_SHORT]);
        }

        $this->addClassProperty($name, $comments, $defaultValue, $attributes, $type, $isNullable);

        $this->addParamToConstructor($name, $type, $defaultValue, $isNullable);

        $this->addStatementToConstructor(
            new Expression(new Assign(
                new PropertyFetch(new Variable('this'), $name),
                new Variable($name)
            ))
        );
    }

    protected function addPromotedProperty(
        string $name,
        $type,
        bool $isNullable,
        array $comments = [],
        array $attributes = [],
        bool $isPrivate = true,
        bool $isReadonly = true
    ) {
        $defaultValue = null;
        if ('array' === $type) {
            $defaultValue = new Array_([], ['kind' => Array_::KIND_SHORT]);
        }

        $this->addParamToConstructor($name, $type, $defaultValue, $isNullable, $isPrivate, $isReadonly);
    }

    /**
     * @param string|Name|NullableType|Identifier $type
     */
    protected abstract function addClassProperty(
        string $name,
        array $annotationLines = [],
        $defaultValue = null,
        array $attributes = [],
        $type = 'string',
        bool $nullable = false
    );
}
