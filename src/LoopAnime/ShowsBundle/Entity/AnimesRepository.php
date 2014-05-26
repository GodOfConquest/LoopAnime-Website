<?php

namespace LoopAnime\ShowsBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * AnimesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnimesRepository extends EntityRepository
{

    public function getAnimesByTitle($title, $orderKey = "title", $order = "ASC") {
        return $this->createQueryBuilder("animes")
            ->select("animes")
            ->where('animes.title LIKE :title')
            ->orderBy("animes.".$orderKey, $order)
            ->setParameter("title", ''.$title.'%')
            ->getQuery()
            ->getResult();
    }

    public function getAnimesRecent() {
        return $this->createQueryBuilder("animes")
            ->select("animes")
            ->orderBy("animes.startTime","DESC")
            ->getQuery()
            ->getResult();
    }

    public function getAnimesMostRated() {
        return $this->createQueryBuilder("animes")
            ->select("animes")
            ->orderBy("animes.ratingUp","DESC")
            ->addOrderBy("animes.ratingCount", "DESC")
            ->getQuery()
            ->getResult();
    }

}
