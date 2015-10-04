<?php

namespace LoopAnime\AppBundle\Queue\Worker\Serie;

use LoopAnime\AppBundle\BusCommand\Anime\CreateLink;
use LoopAnime\AppBundle\Crawler\Enum\AnimeHosterEnum;
use LoopAnime\AppBundle\Crawler\Enum\SerieHosterEnum;
use LoopAnime\AppBundle\Crawler\Service\CrawlerService;
use LoopAnime\AppBundle\Enum\TypeSerieEnum;
use LoopAnime\AppBundle\Queue\Enum\QueueType;
use LoopAnime\AppBundle\Queue\Exception\SubjectNotFoundException;
use LoopAnime\AppBundle\Queue\Exception\WorkerDataMalformedException;
use LoopAnime\AppBundle\Queue\Worker\BaseWorker;
use LoopAnime\AppBundle\Queue\Worker\WorkerInterface;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;

class PopulateEpisodeWorker extends BaseWorker implements WorkerInterface
{
    public function runWorker()
    {
        $data = $this->getData();
        $idEpisode = $data['idEpisode'];

        /** @var AnimesEpisodes $episode */
        $episode = $this->em->getRepository('LoopAnimeShowsBundle:AnimesEpisodes')->find($idEpisode);
        if (!$episode) {
            throw new SubjectNotFoundException('Episode with the id' . $idEpisode . ' was not found!');
        }

        $season = $episode->getSeason();
        $serie = $season->getAnime();
        $crawlerService = $this->getCrawlerService();

        $hosters = AnimeHosterEnum::getAsArray();
        switch ($serie->getTypeSeries()) {
            case TypeSerieEnum::ANIME:
                $hosters = AnimeHosterEnum::getAsArray();
                break;
            case TypeSerieEnum::SERIE:
                $hosters = SerieHosterEnum::getAsArray();
                break;
        }

        foreach ($hosters as $hoster) {
            $this->log('['.$serie->getId().'] Crawling the episode ' . $season->getSeason() . "X" . $episode->getEpisode() . ' title: ' . $episode->getEpisodeTitle());
            $hoster = $crawlerService->getHoster($hoster);
            try {
                $mirrors = $crawlerService->crawlEpisode($episode, $hoster->getName());
                $command = new CreateLink($episode, $hoster, $mirrors, $this->output);
                $this->getContainer()->get('command_bus')->handle($command);
                $this->log("Episode was found with 100 accuracy! Gathered a total of ".count($mirrors)." Mirrors", 'info');
            } catch (\Exception $e) {
                $this->log("Crawler throwed an expcetion: ".$e->getMessage(), 'comment');
            }
        }
        return true;
    }

    /**
     * @return CrawlerService
     */
    public function getCrawlerService()
    {
        /** @var CrawlerService $crawlerService */
        return $this->getContainer()->get('crawler.service');
    }

    public function validate()
    {
        $data = $this->getData();
        $keys = [];
        if (empty($data['idEpisode'])) {
            $keys[] = 'idEpisode';
        }
        if (!empty($keys)) {
            throw new WorkerDataMalformedException($keys);
        }
        return true;
    }

    public function getQueueType()
    {
        return QueueType::POPULATE_EPISODE;
    }
}
