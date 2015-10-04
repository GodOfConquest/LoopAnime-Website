<?php

namespace LoopAnime\AppBundle\Crawler\Strategy;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Persistence\ObjectRepository;
use LoopAnime\AppBundle\Crawler\Service\CrawlerService;
use LoopAnime\CrawlersBundle\Entity\AnimesCrawlers;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;

abstract class AbstractStrategy implements StrategyInterface
{
    protected $cache;
    private $crawlSettingsRepo;
    /** @var CrawlerService */
    protected $crawlerService;

    public function __construct(ObjectRepository $crawlSettingsRepo, ApcCache $cache, CrawlerService $crawlerService)
    {
        $this->crawlSettingsRepo = $crawlSettingsRepo;
        $this->cache = $cache;
        $this->crawlerService = $crawlerService;
    }

    /**
     * @param AnimesEpisodes $episode
     * @return AnimesCrawlers
     */
    protected function getCrawlSettings(AnimesEpisodes $episode)
    {
        return $this->crawlSettingsRepo->findOneBy(['anime' => $episode->getSeason()->getAnime()->getId()]);
    }

    protected function search($searchTerm)
    {

    }

}
