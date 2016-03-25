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
            ->andWhere('l.processed = false')
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
            ->andWhere('l.error = false')
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

    public function findProfileLinksForProcess($profile_id, $limit = 10)
    {
        $qb = $this->createQueryBuilder('l');

        $qb = $qb
            ->select('l')
            ->where('l.profile = :profile_id')
            ->setParameter('profile_id', $profile_id)
            ->andWhere('l.processed = false')
            ->andWhere('l.error = false')
            ->andWhere('l.skip = false')
            ->getQuery()
            ->setMaxResults($limit);
        ;

        return $qb->execute();
    }
}
