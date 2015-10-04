<?php

namespace LoopAnime\AppBundle\Crawler\Strategy;

use LoopAnime\AppBundle\Crawler\Enum\StrategyEnum;
use LoopAnime\AppBundle\Crawler\Guesser\UrlGuesser;
use LoopAnime\AppBundle\Crawler\Hoster\HosterInterface;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;

class SerieSearchStrategy extends AbstractStrategy implements StrategyInterface
{
    /** @var AnimesEpisodes */
    protected $episode;
    /** @var  HosterInterface */
    protected $hoster;

    public function execute(AnimesEpisodes $episode, HosterInterface $hosterInterface, $uri = false)
    {
        $this->episode = $episode;
        $this->hoster = $hosterInterface;
        $this->validate();

        $idAnime = $episode->getSeason()->getAnime()->getId();
        $titles = [];
        // if (empty($this->cache->fetch('sss_' . $hosterInterface->getName() . "_" . $idAnime))) {
            $titles = $this->createAnimeTitles();
            $page = 0;
            $found = false;
            foreach ($titles as $title) {
                $continue = true;
                while ($continue) {
                    $link = $hosterInterface->getNextPage($hosterInterface->search($title), $page);
                    $contents = file_get_contents($link);
                    $guesser = new UrlGuesser($contents, [$episode->getSeason()->getAnime()->getTitle(), $title], $hosterInterface->getDomain());
                    $guesser->guess();
                    $page++;
                    if ($guesser->isExactMatch() && !empty($guesser->getUri())) {
                        $found = true;
                        $this->cache->save('sss_' . $hosterInterface->getName() . "_" . $idAnime, $guesser->getUri());
                        break(2);
                    }
                    if ($found || !$hosterInterface->isPaginated() || $page > 50) {
                        $continue = false;
                    }
                }
            }
        //}
        if (empty($guesser) || !$guesser->isExactMatch()) {
            throw new \Exception("The serie was not found - there was no exact math. Log: " . !empty($guesser) ? $guesser->getLog() : '');
        }
        //$uri = $this->cache->fetch('sss_' . $hosterInterface->getName() . "_" . $idAnime);
        $uri = $guesser->getUri();
        if (empty($uri)) {
            throw new \Exception(sprintf("URI is empty! %s was not found on the hoster %s. Log: %s, URI: %s",
                implode(",", $titles), $hosterInterface->getName(), $guesser->getLog(), $guesser->getUri()));
        }
        /** @var EpisodeSearchStrategy $episodeSearchStrategy */
        $episodeSearchStrategy = $this->crawlerService->getStrategy(StrategyEnum::STRATEGY_EPISODE_SEARCH);
        return $episodeSearchStrategy->execute($episode, $hosterInterface, $uri);
    }

    private function createAnimeTitles()
    {
        $titles = [$this->episode->getSeason()->getAnime()->getTitle()];
        $crawlSettings = $this->getCrawlSettings($this->episode);
        if ($crawlSettings) {
            $seasonSettings = $crawlSettings->getMinimalSeasonSettings($this->episode->getSeason()->getSeason());
            $titles[] = $seasonSettings->getAnimeTitle();
        }

        return $titles;
    }

    private function validate()
    {
        return true;
    }

    public function getName()
    {
        return StrategyEnum::STRATEGY_SERIE_SEARCH;
    }
}
