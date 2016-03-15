<?php

namespace Nz\CrawlerBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProfileRepository extends EntityRepository
{

 
    public function findProfilesForProcess($limit)
    {
        $qb = $this->createQueryBuilder('l');

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
}
