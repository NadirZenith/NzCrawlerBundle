<?php

namespace Nz\CrawlerBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProfileRepository extends EntityRepository
{

    public function findEnabled($limit = 10)
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select('p')
            ->where('p.enabled = true')
        ;

        $query = $qb
            ->getQuery()
            ->setMaxResults($limit)
        ;

        return $query->execute();
    }

   
}
