<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use <?= $manager_full_class_name ?>;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class <?= $class_name ?> implements MutationInterface, AliasedInterface
{
    private <?= $manager_short_name ?> $<?= $manager_property_name ?>;

    public function __construct(<?= $manager_short_name ?> $<?= $manager_property_name ?>)
    {
        $this-><?= $manager_property_name ?> = $<?= $manager_property_name ?>;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function create<?= $domain_model ?>(array $data): array
    {
        $<?= $object_name ?> = $this-><?= $manager_property_name ?>->create($data);

        return [
            '<?= $object_name ?>' => $<?= $object_name ?>,
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function update<?= $domain_model ?>(array $data): array
    {
        $<?= $object_name ?> = $this-><?= $manager_property_name ?>->update($data);

        return [
            '<?= $object_name ?>' => $<?= $object_name ?>,
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function delete<?= $domain_model ?>(array $data): array
    {
        $<?= $object_name ?>Id = $this-><?= $manager_property_name ?>->delete($data);

        return [
            'id' => $<?= $object_name ?>Id,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'create<?= $domain_model ?>' => 'Create<?= $domain_model ?>',
            'update<?= $domain_model ?>' => 'Update<?= $domain_model ?>',
            'delete<?= $domain_model ?>' => 'Delete<?= $domain_model ?>',
        ];
    }
}
