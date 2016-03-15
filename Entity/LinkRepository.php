<?php

namespace Nz\CrawlerBundle\Entity;

use Doctrine\ORM\EntityRepository;

class LinkRepository extends EntityRepository
{

    public function findFromHost($host)
    {
        $qb = $this->createQueryBuilder('l');

        $query = $qb
            ->select('l')
            ->where($qb->expr()->like('l.url', ':host'))
            ->setParameter('host', '%' . $host . '%')
            ->getQuery()
        ;

        return $query->execute();
    }

    public function findLinksForProcess($limit)
    {
        $qb = $this->createQueryBuilder('l');

        $qb
            ->select('l')
            ->where('l.processed = false')
            ->andWhere('l.hasError = false')
            ->andWhere('l.skip = false')
        ;

        if ($limit) {
            $qb
                ->setMaxResults($limit)
            ;
        }

        $query = $qb
            ->getQuery()
        ;

        return $query->execute();
    }
}
