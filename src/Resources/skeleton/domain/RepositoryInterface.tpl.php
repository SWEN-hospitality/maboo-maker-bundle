<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use <?= $domain_model_full_class_name ?>;
use <?= $create_write_model_full_class_name ?> as <?= $create_write_model_alias ?>;
use <?= $update_write_model_full_class_name ?> as <?= $update_write_model_alias ?>;

interface <?= $class_name . "\n" ?>
{
    /** @return <?= $domain_model_short_name ?>[] */
    public function fetchAll(): array;

    public function fetchOne(string $id): ?<?= $domain_model_short_name ?>;

    public function exists(string $id): bool;

    public function add(<?= $create_write_model_alias ?> $<?= $object_name ?>): <?= $domain_model_short_name ?>;

    public function update(<?= $update_write_model_alias ?> $<?= $object_name ?>): <?= $domain_model_short_name ?>;

    public function delete(string $id): void;
}
