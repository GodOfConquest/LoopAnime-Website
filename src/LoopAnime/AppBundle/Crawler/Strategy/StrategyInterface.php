<?php

namespace LoopAnime\AppBundle\Crawler\Strategy;

use LoopAnime\AppBundle\Crawler\Hoster\HosterInterface;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;

interface StrategyInterface
{

    public function execute(AnimesEpisodes $episode, HosterInterface $hoster, $uri = false);

    public function getName();

}
