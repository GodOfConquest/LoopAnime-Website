<?php

namespace LoopAnime\AppBundle\Crawler\Service;

use Doctrine\Common\Persistence\ObjectManager;
use LoopAnime\AppBundle\Crawler\Exception\InvalidHosterException;
use LoopAnime\AppBundle\Crawler\Exception\InvalidStrategyException;
use LoopAnime\AppBundle\Crawler\Guesser\GuesserInterface;
use LoopAnime\AppBundle\Crawler\Hoster\HosterInterface;
use LoopAnime\AppBundle\Crawler\Strategy\StrategyInterface;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;

class CrawlerService
{
    /** @var StrategyInterface[] */
    protected $strategies;
    /** @var HosterInterface[] */
    private $hosters;
    private $guesser;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param AnimesEpisodes $animeEpisodes
     * @param $hoster
     * @return
     * @throws \Exception
     */
    public function crawlEpisode(AnimesEpisodes $animeEpisodes, $hoster)
    {
        if (!$hoster instanceof HosterInterface) {
            $hoster = $this->getHoster($hoster);
        }
        $strategy = $this->getStrategy($hoster->getStrategy());
        /** @var GuesserInterface $guesser */
        $guesser = $strategy->execute($animeEpisodes, $hoster);

        if (!$guesser->isExactMatch()) {
            throw new \Exception('Not the best match - ' . $guesser->getLog());
        }
        $this->guesser = $guesser;

        return $hoster->getEpisodeMirrors($guesser->getUri());
    }

    /**
     * @return bool|GuesserInterface
     */
    public function getLastGuesser()
    {
        if (empty($this->guesser)) {
            return false;
        }
        return $this->guesser;
    }

    public function addHoster(HosterInterface $hoster)
    {
        $this->hosters[$hoster->getName()] = $hoster;
    }

    public function addStrategy(StrategyInterface $strategy)
    {
        $this->strategies[$strategy->getName()] = $strategy;
    }

    public function getHoster($hoster)
    {
        if (!isset($this->hosters[$hoster])) {
            throw new InvalidHosterException($hoster);
        }
        return $this->hosters[$hoster];
    }

    public function getStrategy($strategy)
    {
        if (!isset($this->strategies[$strategy])) {
            throw new InvalidStrategyException($strategy);
        }
        return $this->strategies[$strategy];
    }

}
