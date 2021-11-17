<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use <?= $validator_full_class_name ?>;
use <?= $domain_model_full_class_name ?>;
use <?= $repository_interface_full_class_name ?>;
use <?= $create_write_model_full_class_name ?> as <?= $create_write_model_alias ?>;
use <?= $update_write_model_full_class_name ?> as <?= $update_write_model_alias ?>;
use App\Shared\Application\Exception\ValidationException;
use App\Shared\Application\Factory\UuidGeneratorInterface;

class <?= $class_name . "\n" ?>
{
    private UuidGeneratorInterface $uuidGenerator;
    private <?= $repository_interface_short_name ?> $<?= $repository_property_name ?>;
    private <?= $validator_short_name ?> $<?= $validator_property_name ?>;

    public function __construct(
        UuidGeneratorInterface $uuidGenerator,
        <?= $repository_interface_short_name ?> $<?= $repository_property_name . "\n" ?>,
        <?= $validator_short_name ?> $<?= $validator_property_name . "\n" ?>
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this-><?= $repository_property_name ?> = $<?= $repository_property_name ?>;
        $this-><?= $validator_property_name ?> = $<?= $validator_property_name ?>;
    }

    /**
     * @param array<string, mixed> $rawPayload
     */
    public function create(array $rawPayload): <?= $domain_model_short_name . "\n" ?>
    {
        $errors = $this-><?= $validator_property_name ?>->validateCreate($rawPayload);
        if ($errors->isEmpty() === false) {
            throw new ValidationException($errors);
        }

        $<?= $object_name ?> = new <?= $create_write_model_alias ?>(
            $this->uuidGenerator->generate(),
<?php foreach ($fields as $idx => $field): ?>
<?php if ('id' !== $field->name): ?>
            $rawPayload['<?= $field->name ?>']<?= true === $field->isNullable ? " ?? null" : "" ?><?= $idx < $fields_count - 1 ? ",\n" : "\n" ?>
<?php endif ?>
<?php endforeach; ?>
        );

        return $this-><?= $repository_property_name ?>->add($<?= $object_name ?>);
    }

    /**
     * @param array<string, mixed> $rawPayload
     */
    public function update(array $rawPayload): <?= $domain_model_short_name . "\n" ?>
    {
        $errors = $this-><?= $validator_property_name ?>->validateUpdate($rawPayload);
        if ($errors->isEmpty() === false) {
            throw new ValidationException($errors);
        }

        $<?= $object_name ?> = new <?= $update_write_model_alias ?>(
<?php foreach ($fields as $idx => $field): ?>
            $rawPayload['<?= $field->name ?>']<?= true === $field->isNullable ? " ?? null" : "" ?><?= $idx < $fields_count - 1 ? ",\n" : "\n" ?>
<?php endforeach; ?>
        );

        return $this-><?= $repository_property_name ?>->update($<?= $object_name ?>);
    }

    /**
     * @param array<string, mixed> $rawPayload
     */
    public function delete(array $rawPayload): string
    {
        $errors = $this-><?= $validator_property_name ?>->validateDelete($rawPayload);
        if ($errors->isEmpty() === false) {
            throw new ValidationException($errors);
        }

        $<?= $object_name ?>Id = $rawPayload['id'];

        $this-><?= $repository_property_name ?>->delete($<?= $object_name ?>Id);

        return $<?= $object_name ?>Id;
    }
}
