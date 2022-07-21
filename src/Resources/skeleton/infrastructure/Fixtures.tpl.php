<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?> as <?= $entity_alias ?>;
use App\Shared\Application\Factory\UuidGeneratorInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class <?= $class_name ?> extends Fixture implements FixtureGroupInterface
{
    public function __construct(private readonly UuidGeneratorInterface $uuidGenerator)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getData() as $<?= $object_name ?>Data) {
            $id = $<?= $object_name ?>Data['id'] ?? $this->uuidGenerator->generate();
            $<?= $object_name ?> = new <?= $entity_alias ?>(
                $id,
<?php foreach ($field_names as $idx => $field): ?>
                $<?= $object_name ?>Data['<?= $field ?>']<?= $idx < $fields_count - 1 ? ",\n" : "\n" ?>
<?php endforeach; ?>
            );

            $manager->persist($<?= $object_name ?>);

            $this->setReference($id, $<?= $object_name ?>);
        }

        $manager->flush();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getData(): array
    {
        return [
            [
<?php foreach ($field_names as $idx => $field): ?>
                '<?= $field ?>' => <?= $field_fixture_values[$idx] ?>,
<?php endforeach; ?>
            ],
        ];
    }

    public static function getGroups(): array
    {
        return [
           'default',
       ];
    }
}
