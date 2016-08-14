<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PostRepository extends EntityRepository
{
    public function getLatestPost($limit = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->addOrderBy('p.id', 'DESC');

        if (false === is_null($limit))
            $qb->setMaxResults($limit);

        return $qb->getQuery()
            ->getResult();
    }

    public function getPostCount()
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select($qb->expr()->count('p.id'));

        return $qb->getQuery()->getSingleScalarResult();
    }
}