<?php

namespace Nz\CrawlerBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProfileRepository extends EntityRepository
{

    public function findEnabled($limit)
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select('p')
            ->where('p.enabled = true')
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

    public function findProfileLinks($profile_id, $limit = 10)
    {
        $qb = $this->createQueryBuilder('p');

        $qb = $qb
            ->select('p', 'l')
            ->join('p.links', 'l')
            ->where('p.id = :profile_id')
            ->setParameter('profile_id', $profile_id)
            ->andWhere('l.processed = false')
            ->andWhere('l.error = false')
            ->andWhere('l.skip = false')
            ->getQuery()
        ;

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        $profile = $qb
            ->getOneOrNullResult()
        ;

        return $profile ? $profile->getLinks() : array();
    }
}
