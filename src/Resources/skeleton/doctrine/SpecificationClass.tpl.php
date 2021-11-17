<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use <?= $specification_interface_full_class_name ?>;
use <?= $repository_interface_full_class_name ?>;

class <?= $class_name ?> implements <?= $specification_interface_short_name . "\n"?>
{
    private <?= $repository_interface_short_name ?> $<?= $repository_property_name ?>;

    public function __construct(<?= $repository_interface_short_name ?> $<?= $repository_property_name ?>)
    {
        $this-><?= $repository_property_name ?> = $<?= $repository_property_name ?>;
    }

    public function satisfiedBy(string $id): bool
    {
        return $this-><?= $repository_property_name ?>->exists($id);
    }
}
