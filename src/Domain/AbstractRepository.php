<?php declare(strict_types=1);

namespace App\Domain;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Ramsey\Uuid\Uuid;

abstract class AbstractRepository extends EntityRepository
{
    /**
     * @param string|Uuid $uuid
     *
     * @return null|int|mixed|string
     */
    public function findOneByUuid($uuid)
    {
        if (Uuid::isValid((string) $uuid)) {
            $query = $this->createQueryBuilder('en')
                ->andWhere('en.uuid = :uuid')->setParameter('uuid', (string) $uuid, \Ramsey\Uuid\Doctrine\UuidType::NAME)
                ->getQuery();

            try {
                $result = $query->getOneOrNullResult();
            } catch (NonUniqueResultException $e) {
                $results = $query->getResult();
                $result = array_shift($results);
            }

            return $result;
        }

        return null;
    }
}
