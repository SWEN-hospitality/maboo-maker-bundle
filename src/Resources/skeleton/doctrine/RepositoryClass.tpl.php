<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use <?= $domain_model_full_class_name ?>;
use <?= $repository_interface_full_class_name ?>;
use <?= $create_write_model_full_class_name ?> as <?= $create_write_model_alias ?>;
use <?= $update_write_model_full_class_name ?> as <?= $update_write_model_alias ?>;
use <?= $entity_full_class_name ?> as <?= $entity_alias ?>;
use <?= $entity_mapper_full_class_name ?>;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class <?= $class_name ?> implements <?= $repository_interface_short_name . "\n"?>
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** @return <?= $domain_model_short_name ?>[] */
    public function fetchAll(): array
    {
        $results = $this->entityManager->createQueryBuilder()
            ->select('<?= $db_table_alias ?>')
            ->from(<?= $entity_alias ?>::class, '<?= $db_table_alias ?>')
            ->getQuery()
            ->getResult();

        return array_map(
            fn (<?= $entity_alias ?> $<?= $object_name ?>) => <?= $entity_mapper_short_name ?>::fromEntityToModel($<?= $object_name ?>),
            $results
        );
    }

    public function fetchOne(string $id): ?<?= $domain_model_short_name . "\n" ?>
    {
        try {
            $result = $this->entityManager->createQueryBuilder()
                ->select('<?= $db_table_alias ?>')
                ->from(<?= $entity_alias ?>::class, '<?= $db_table_alias ?>')
                ->where('<?= $db_table_alias ?>.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();

            return <?= $entity_mapper_short_name ?>::fromEntityToModel($result);
        } catch (NoResultException) {
            return null;
        }
    }

    public function exists(string $id): bool
    {
        try {
            $this->entityManager->createQueryBuilder()
                ->select('1')
                ->from(<?= $entity_alias ?>::class, '<?= $db_table_alias ?>')
                ->where('<?= $db_table_alias ?>.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult();

            return true;
        } catch (NoResultException) {
            return false;
        }
    }

    public function add(<?= $create_write_model_alias ?> $<?= $object_name ?>): <?= $domain_model_short_name . "\n" ?>
    {
<?php foreach($foreignKeys as $key => $props): ?>
<?php if ($props['nullable'] === true): ?>
        $<?= $props['name'] ?> = null;
        if (null !== $<?= $object_name ?>-><?= $props['domainFieldName'] ?>) {
            $<?= $props['name'] ?> = $this->entityManager->getReference(<?= $props['entityAlias'] ?>::class, $<?= $object_name ?>-><?= $props['domainFieldName'] ?>);
            assert($<?= $props['name'] ?> instanceof <?= $props['entityAlias'] ?>);
        }
<?php else: ?>
        $<?= $props['name'] ?> = $this->entityManager->getReference(<?= $props['entityAlias'] ?>::class, $<?= $object_name ?>-><?= $props['domainFieldName'] ?>);
        assert($<?= $props['name'] ?> instanceof <?= $props['entityAlias'] ?>);
<?php endif; ?>
<?= "\n" ?>
<?php endforeach; ?>
        $entity = new <?= $entity_alias ?>(
<?php foreach ($fields as $idx => $field): ?>
<?php if (true === in_array($field, array_keys($foreignKeys))): ?>
            $<?= $foreignKeys[$field]['name'] ?><?= ",\n" ?>
<?php else: ?>
            $<?= $object_name ?>-><?= $field ?><?= ",\n" ?>
<?php endif ?>
<?php endforeach; ?>
        );

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return <?= $entity_mapper_short_name ?>::fromEntityToModel($entity);
    }

    public function update(<?= $update_write_model_alias ?> $<?= $object_name ?>): <?= $domain_model_short_name . "\n" ?>
    {
<?php foreach($foreignKeys as $key => $props): ?>
<?php if ($props['nullable'] === true): ?>
        $<?= $props['name'] ?> = null;
        if (null !== $<?= $object_name ?>-><?= $props['domainFieldName'] ?>) {
            $<?= $props['name'] ?> = $this->entityManager->getReference(<?= $props['entityAlias'] ?>::class, $<?= $object_name ?>-><?= $props['domainFieldName'] ?>);
            assert($<?= $props['name'] ?> instanceof <?= $props['entityAlias'] ?>);
        }
<?php else: ?>
        $<?= $props['name'] ?> = $this->entityManager->getReference(<?= $props['entityAlias'] ?>::class, $<?= $object_name ?>-><?= $props['domainFieldName'] ?>);
        assert($<?= $props['name'] ?> instanceof <?= $props['entityAlias'] ?>);
<?php endif; ?>
<?= "\n" ?>
<?php endforeach; ?>
        $entity = $this->entityManager->find(<?= $entity_alias ?>::class, $<?= $object_name ?>->id);
        assert($entity instanceof <?= $entity_alias ?>);

<?php foreach ($fields as $idx => $field): ?>
<?php if ('id' === $field): ?>
<?php continue; ?>
<?php endif ?>
<?php if (true === in_array($field, array_keys($foreignKeys))): ?>
        $entity-><?= $field_setters[$idx] ?>($<?= $foreignKeys[$field]['name'] ?>);
<?php else: ?>
        $entity-><?= $field_setters[$idx] ?>($<?= $object_name ?>-><?= $field ?>);
<?php endif ?>
<?php endforeach; ?>

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return <?= $entity_mapper_short_name ?>::fromEntityToModel($entity);
    }

    public function delete(string $id): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(<?= $entity_alias ?>::class, '<?= $db_table_alias ?>')
            ->where('<?= $db_table_alias ?>.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
}
