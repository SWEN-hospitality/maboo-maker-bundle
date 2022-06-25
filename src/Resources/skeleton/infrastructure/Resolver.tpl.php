<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use <?= $domain_model_full_class_name ?>;
use <?= $repository_interface_full_class_name ?>;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

class <?= $class_name ?> implements QueryInterface, AliasedInterface
{
    public function __construct(private readonly <?= $repository_interface_short_name ?> $<?= $repository_property_name ?>)
    {
    }

    public function resolveOne(string $id): ?<?= $domain_model_short_name . "\n" ?>
    {
        return $this-><?= $repository_property_name ?>->fetchOne($id);
    }

    /**
     * @return <?= $domain_model_short_name ?>[]
     */
    public function resolveAll(): array
    {
        return $this-><?= $repository_property_name ?>->fetchAll();
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolveOne' => '<?= $resource_name ?>',
            'resolveAll' => '<?= $collection_name ?>',
        ];
    }
}
