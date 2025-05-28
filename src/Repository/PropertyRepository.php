<?php

namespace App\Repository;

use App\Entity\Property;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Property>
 */
class PropertyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Property::class);
    }

    public function save(Property $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Property $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //buscar por tipo de contrato
    public function findByTipoContrato(string $tipoContrato): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.tipo_contrato = :tipoContrato')
            ->setParameter('tipoContrato', $tipoContrato)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
