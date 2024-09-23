<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Services\ClassGenerator\Application;

use Bornfight\MabooMakerBundle\Services\ClassManipulator\EntityField;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class ValidatorClassGenerator
{
    public function __construct(private Generator $generator)
    {
    }

    /**
     * @param EntityField[] $fields
     */
    public function generateValidatorClass(
        ClassNameDetails $validatorClassDetails,
        ClassNameDetails $domainModelClassDetails,
        ClassNameDetails $specificationClassDetails,
        array $fields
    ): string {
        $validationRules = [];
        $isStringRuleRequired = false;
        $isNotEmptyStringRuleRequired = false;
        $isBoolRuleRequired = false;
        $isNotEmptyBoolRuleRequired = false;
        $isNumericRuleRequired = false;
        $isNotEmptyNumericRuleRequired = false;
        $isIntegerRuleRequired = false;
        $isNotEmptyIntegerRuleRequired = false;


        foreach ($fields as $field) {
            switch (true) {
                case 'int' === $field->typeHint && false === $field->isNullable:
                    $validationRules[] = 'int_required';
                    $isNotEmptyIntegerRuleRequired = true;
                    break;
                case 'int' === $field->typeHint:
                    $validationRules[] = 'int';
                    $isIntegerRuleRequired = true;
                    break;
                case 'float' === $field->typeHint && false === $field->isNullable:
                    $validationRules[] = 'numeric_required';
                    $isNotEmptyNumericRuleRequired = true;
                    break;
                case 'float' === $field->typeHint:
                    $validationRules[] = 'numeric';
                    $isNumericRuleRequired = true;
                    break;
                case 'string' === $field->typeHint && false === $field->isNullable:
                    $validationRules[] = 'str_required';
                    $isNotEmptyStringRuleRequired = true;
                    break;
                case 'string' === $field->typeHint:
                    $validationRules[] = 'str';
                    $isStringRuleRequired = true;
                    break;
                case 'bool' === $field->typeHint && false === $field->isNullable:
                    $validationRules[] = 'bool_required';
                    $isNotEmptyBoolRuleRequired = true;
                    break;
                case 'bool' === $field->typeHint:
                    $validationRules[] = 'bool';
                    $isBoolRuleRequired = true;
                    break;
                default:
                    $validationRules[] = 'not_supported';
            }
        }
        
        return $this->generator->generateClass(
            $validatorClassDetails->getFullName(),
            __DIR__ . '/../../../Resources/skeleton/application/Validator.tpl.php',
            [
                'specification_full_class_name' => $specificationClassDetails->getFullName(),
                'specification_short_name' => $specificationClassDetails->getShortName(),
                'specification_property_name' => Str::asLowerCamelCase($specificationClassDetails->getShortName()),
                'resource' => $domainModelClassDetails->getShortName(),
                'fields_count' => count($fields),
                'fields' => $fields,
                'validation_rules' => $validationRules,
                'use_is_string_rule' => $isStringRuleRequired,
                'use_is_not_empty_string_rule' => $isNotEmptyStringRuleRequired,
                'use_is_integer_rule' => $isIntegerRuleRequired,
                'use_is_not_empty_integer_rule' => $isNotEmptyIntegerRuleRequired,
                'use_is_numeric_rule' => $isNumericRuleRequired,
                'use_is_not_empty_numeric_rule' => $isNotEmptyNumericRuleRequired,
                'use_is_bool_rule' => $isBoolRuleRequired,
                'use_is_not_empty_bool_rule' => $isNotEmptyBoolRuleRequired,
            ]
        );
    }
}
