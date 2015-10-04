<?php

namespace LoopAnime\AppBundle\Queue\Entity;

use LoopAnime\AppBundle\Entity\BaseRepository;
use LoopAnime\AppBundle\Queue\Enum\QueueStatus;

class QueueRepository extends BaseRepository
{

    /**
     * @param $limit
     * @return Queue[]|null
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getPendingJobs($limit)
    {
        $query = $this->createQueryBuilder('q')
                ->select('q')
                ->where('q.status = :st')
                ->setParameter('st', QueueStatus::PENDING)
                ->setMaxResults($limit)
        ;
        return $query->getQuery()->execute();
    }

    public function getProcessingJobs()
    {
        $query = $this->createQueryBuilder('q')
            ->select('q')
            ->where('q.status = :st')
            ->setParameter('st', QueueStatus::PROCESSING);
        ;
        return $query->getQuery()->execute();
    }
}
