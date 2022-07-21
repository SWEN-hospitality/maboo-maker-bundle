<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use <?= $specification_full_class_name ?>;
use App\Shared\Application\Validator\ObjectContainer;
use App\Shared\Application\Validator\Payload;
use App\Shared\Application\Validator\Rule\Callback;
<?php if (true == $use_is_bool_rule): ?>
use App\Shared\Application\Validator\Rule\Type\IsBool;
<?php endif ?>
<?php if (true == $use_is_integer_rule): ?>
use App\Shared\Application\Validator\Rule\Type\IsInteger;
<?php endif ?>
<?php if (true == $use_is_numeric_rule): ?>
use App\Shared\Application\Validator\Rule\Type\IsNumeric;
<?php endif ?>
<?php if (true == $use_is_string_rule): ?>
use App\Shared\Application\Validator\Rule\Type\IsString;
<?php endif ?>
use App\Shared\Application\Validator\RuleSet;
use App\Shared\Application\Validator\RuleSet\FieldRuleSet;
use App\Shared\Application\Validator\Sequence;
<?php if (true == $use_is_not_empty_bool_rule): ?>
use App\Shared\Application\Validator\Sequence\NotEmptyBoolSequence;
<?php endif ?>
<?php if (true == $use_is_not_empty_integer_rule): ?>
use App\Shared\Application\Validator\Sequence\NotEmptyIntegerSequence;
<?php endif ?>
<?php if (true == $use_is_not_empty_numeric_rule): ?>
use App\Shared\Application\Validator\Sequence\NotEmptyNumericSequence;
<?php endif ?>
use App\Shared\Application\Validator\Sequence\NotEmptyStringSequence;
use App\Shared\Application\Validator\ValidationErrorList;
use App\Shared\Application\Validator\Validator;
use App\Shared\Application\Validator\ValidatorException;

class <?= $class_name ?> extends Validator
{
    public function __construct(
        private readonly <?= $specification_short_name ?> $<?= $specification_property_name . "\n" ?>
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function validateCreate(array $payload): ValidationErrorList
    {
        $rules = new ObjectContainer(
            $this->validateCommonRuleSets()
        );

        return $this->validate(new Payload($payload), $rules);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function validateUpdate(array $payload): ValidationErrorList
    {
        $rules = new ObjectContainer([
            $this->validateIdRuleSet(),
            ...$this->validateCommonRuleSets(),
        ]);

        return $this->validate(new Payload($payload), $rules);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function validateDelete(array $payload): ValidationErrorList
    {
        $rules = new ObjectContainer([
            $this->validateIdRuleSet(),
        ]);

        return $this->validate(new Payload($payload), $rules);
    }

    /**
     * @return RuleSet[]
     */
    private function validateCommonRuleSets(): array
    {
        return [
<?php foreach ($fields as $idx => $field): ?>
<?php if ('id' !== $field->name): ?>
            new FieldRuleSet('<?= $field->name ?>', [
<?php if ('bool_required' === $validation_rules[$idx]): ?>
                new NotEmptyBoolSequence(),
<?php endif ?>
<?php if ('int_required' === $validation_rules[$idx]): ?>
                new NotEmptyIntegerSequence(),
<?php endif ?>
<?php if ('numeric_required' === $validation_rules[$idx]): ?>
                new NotEmptyNumericSequence(),
<?php endif ?>
<?php if ('str_required' === $validation_rules[$idx]): ?>
                new NotEmptyStringSequence(),
<?php endif ?>
<?php if ('bool' === $validation_rules[$idx]): ?>
                new Sequence([
                    new IsBool(),
                ]),
<?php endif ?>
<?php if ('int' === $validation_rules[$idx]): ?>
                new Sequence([
                    new IsInteger(),
                ]),
<?php endif ?>
<?php if ('numeric' === $validation_rules[$idx]): ?>
                new Sequence([
                    new IsNumeric(),
                ]),
<?php endif ?>
<?php if ('str' === $validation_rules[$idx]): ?>
                new Sequence([
                    new IsString(),
                ]),
<?php endif ?>
            ]),
<?php endif ?>
<?php endforeach; ?>
        ];
    }

    private function validateIdRuleSet(): FieldRuleSet
    {
        return new FieldRuleSet('id', [
            new NotEmptyStringSequence(),
            new Sequence([
                new Callback(function (string $value) {
                    if ($this-><?= $specification_property_name ?>->satisfiedBy($value) === false) {
                        throw new ValidatorException('<?= $resource ?> does not exist');
                    }
                }),
            ]),
        ]);
    }
}
