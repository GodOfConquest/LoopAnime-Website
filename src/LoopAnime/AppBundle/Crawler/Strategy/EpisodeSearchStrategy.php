<?php

namespace LoopAnime\AppBundle\Crawler\Strategy;

use LoopAnime\AppBundle\Crawler\Enum\StrategyEnum;
use LoopAnime\AppBundle\Crawler\Guesser\GuesserInterface;
use LoopAnime\AppBundle\Crawler\Guesser\UrlGuesser;
use LoopAnime\AppBundle\Crawler\Hoster\HosterInterface;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;

class EpisodeSearchStrategy extends AbstractStrategy implements StrategyInterface
{
    /** @var  HosterInterface */
    protected $hosterInterface;
    /** @var  AnimesEpisodes */
    protected $episode;
    protected $uri;
    protected $episodeTitles;

    public function execute(AnimesEpisodes $episode, HosterInterface $hosterInterface, $uri = false)
    {
        $this->episode = $episode;
        $this->hosterInterface = $hosterInterface;
        $this->uri = $uri;
        $this->validate();
        $this->createEpisodeTitles();

        $bestGuesser = false;
        $page = 0;
        $continue = true;
        if ($uri) {
            while ($continue) {
                $page++;
                $guesser = $this->guess($uri, $page);
                if (!$bestGuesser || ($bestGuesser instanceof GuesserInterface && $bestGuesser->getCompPercentage() < $guesser->getCompPercentage())) {
                    $bestGuesser = $guesser;
                }
                if ($guesser->isExactMatch() && !empty($guesser->getUri())) {
                    return $guesser;
                }
                if (!$hosterInterface->isPaginated() || $page > 50) {
                    $continue = false;
                }
            }
        } else {
            foreach ($this->episodeTitles as $episodeTitle) {
                $uri = $hosterInterface->search($episodeTitle);
                $page = 0;
                $continue = true;
                while ($continue) {
                    $guesser = $this->guess($uri, $page);
                    $page++;
                    if (!$bestGuesser || ($bestGuesser instanceof GuesserInterface && $bestGuesser->getCompPercentage() < $guesser->getCompPercentage())) {
                        $bestGuesser = $guesser;
                    }
                    if ($guesser->isExactMatch() && !empty($guesser->getUri())) {
                        return $guesser;
                    }
                    if (!$hosterInterface->isPaginated() || $page > 50) {
                        $continue = false;
                    }
                }
            }
        }

        return $bestGuesser;
    }

    private function guess($uri, $page)
    {
        $link = $this->hosterInterface->getNextPage($uri, $page);
        $crawlSettings = $this->getCrawlSettings($this->episode);
        $removal = [];
        if ($crawlSettings) {
            $removal = explode(",", $crawlSettings->getEpisodeClean());
        }
        if (empty($link)) {
            throw new \Exception('Link returned by next page cannot be empty. Uri: ' . $uri . ' Page: ' . $page);
        }
        $contents = file_get_contents($link);
        $guesser = new UrlGuesser($contents, $this->episodeTitles, $this->hosterInterface->getDomain());
        $guesser->guess($removal);
        return $guesser;
    }

    private function createEpisodeTitles()
    {
        $uniqueTitles = [$this->episode->getSeason()->getAnime()->getTitle(), $this->episode->getSeason()->getAnime()->getTitle() . " episode"];
        if (!empty($this->uri)) {
            $last = basename($this->uri);
            $uniqueTitles[] = $last;
            $uniqueTitles[] = $last . " episode";
        }
        // Grabbing the absolute number
        $absoluteNumber = $this->episode->getAbsoluteNumber();

        $crawlSettings = $this->getCrawlSettings($this->episode);
        if ($crawlSettings) {
            $seasonSettings = $crawlSettings->getMinimalSeasonSettings($this->episode->getSeason()->getSeason());
            if ($seasonSettings) {
                $uniqueTitles =
                    array_unique(
                        array_filter(
                            array_merge(
                                $uniqueTitles,
                                explode(",", $seasonSettings->getEpisodeTitle()),
                                explode(",", $seasonSettings->getAnimeTitle())
                            )
                        )
                    );
                if ($seasonSettings->getReset() && $seasonSettings->getSeason() === $this->episode->getSeason()->getSeason()) {
                    $absoluteNumber = $this->episode->getEpisode();
                }
                if (!empty($seasonSettings->getHandicap())) {
                    $absoluteNumber += $seasonSettings->getHandicap();
                }
            }
        }

        $episodeTitles = [];
        // Adding the number of the episode to the title
        foreach ($uniqueTitles as $title) {
            // Create possible titles also with leading zeros
            $episodeTitles = array_merge($episodeTitles, $this->leadingZerosGuesses($title, $absoluteNumber));

            // What if bundle of episodes ? 01-02 but never 02-03 ?
            $episodeTitles = array_merge($episodeTitles, $this->leadingZerosGuesses($title, $absoluteNumber, true));
            if ($this->episode->getEpisode() > 1) { // also checks (episode - 1) if episode is 2 e want to grab the 01-02
                $episodeTitles = array_merge($episodeTitles, $this->leadingZerosGuesses($title, $absoluteNumber - 1, true));
            }
        }

        $this->episodeTitles = array_unique($episodeTitles);
    }

    private function leadingZerosGuesses($title, $episode, $addNext = false)
    {
        $guesses = [];
        $lengthEpisode = strlen($episode);
        for ($i = $lengthEpisode; $i <= 4; $i++) {
            $episodeTitle = $title . " " . str_pad($episode, $i, "0", STR_PAD_LEFT);
            if ($addNext) {
                $episodeTitle .= "-" . str_pad($episode + 1, $i, "0", STR_PAD_LEFT);
            }
            $guesses[] = $episodeTitle;
        }

        return $guesses;
    }

    private function validate()
    {
        if ($this->hosterInterface->getStrategy() === StrategyEnum::STRATEGY_SERIE_SEARCH && !$this->uri) {
            throw new \Exception("URI needs to be set when STRATEGY_SERIE_SEARCH is used");
        }
    }

    public function getName()
    {
        return StrategyEnum::STRATEGY_EPISODE_SEARCH;
    }
}
