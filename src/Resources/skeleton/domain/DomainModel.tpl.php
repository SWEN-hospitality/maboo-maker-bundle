<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

class <?= $class_name."\n" ?>
{
    public function __construct(
        private readonly string $id
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
