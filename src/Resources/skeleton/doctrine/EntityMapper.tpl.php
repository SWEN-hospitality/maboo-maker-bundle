<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?> as <?= $entity_alias ?>;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class <?= $class_name."\n" ?>
{
    public static function fromEntityToModel(<?= $entity_alias ?> $entity): <?= $domain_model."\n" ?>
    {
        return new <?= $domain_model ?>(
<?php foreach ($fields as $idx => $field): ?>
            $entity->get<?= $field ?>()<?= ",\n" ?>
<?php endforeach; ?>
        );
    }
}
