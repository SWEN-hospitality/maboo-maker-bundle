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
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;

abstract class ClassManipulator extends ClassSourceManipulator
{
    /**
     * @return EntityField[]
     */
    public function getAllFields(): array
    {
        $properties = array_filter(
            $this->getClassNode()->stmts,
            fn ($stmt) => $stmt instanceof Property
        );

        $propertyFields = array_map(
            fn (Property $property) => EntityField::fromProperty($property),
            $properties
        );

        $constructorNode = $this->getConstructorNode();
        $propagatedConstructorParams = array_filter(
            $constructorNode->getParams(),
            fn ($param) => $param instanceof Param && true === (bool) $param->flags & Class_::VISIBILITY_MODIFIER_MASK
        );

        $paramFields = array_map(
            fn (Param $param) => EntityField::fromParam($param),
            $propagatedConstructorParams
        );

        return array_merge($propertyFields, $paramFields);
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

    protected function addClassFieldAsPromotedProperty(
        string $name,
        $type,
        bool $isNullable,
        array $comments = []
    ): void {
        $defaultValue = null;
        if ('array' === $type) {
            $defaultValue = new Array_([], ['kind' => Array_::KIND_SHORT]);
        }

        $this->addParamToConstructor($name, $type, $defaultValue, $isNullable, true, false, false, $comments);
    }

    protected function addPromotedProperty(
        string $name,
        $type,
        bool $isNullable,
        array $comments = [],
        array $attributes = [],
        bool $isPrivate = true,
        bool $isPublic = false,
        bool $isReadonly = true
    ) {
        $defaultValue = null;
        if ('array' === $type) {
            $defaultValue = new Array_([], ['kind' => Array_::KIND_SHORT]);
        }

        $this->addParamToConstructor($name, $type, $defaultValue, $isNullable, $isPrivate, $isPublic, $isReadonly);
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
