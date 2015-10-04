<?php

namespace LoopAnime\AppBundle\Queue\Worker\Serie;

use LoopAnime\AppBundle\BusCommand\Anime\CreateAnime;
use LoopAnime\AppBundle\BusCommand\Anime\EditAnime;
use LoopAnime\AppBundle\Parser\Implementation\TheTVDB;
use LoopAnime\AppBundle\Parser\ParserAnime;
use LoopAnime\AppBundle\Queue\Enum\QueueType;
use LoopAnime\AppBundle\Queue\Worker\BaseWorker;
use LoopAnime\AppBundle\Queue\Worker\WorkerInterface;
use LoopAnime\ShowsAPIBundle\Entity\AnimesAPI;

class AddSerieWorker extends BaseWorker implements WorkerInterface
{

    public function runWorker()
    {
        $data = $this->getData();
        $tvdbId = $data['tvdbId'];

        $doctrine = $this->getContainer()->get('doctrine');
        /** @var TheTVDB $theTVDb */
        $theTVDb = $this->getContainer()->get('loopanime.parser.tvdb');

        $this->log('Parsing the Content...');
        /** @var ParserAnime $parserAnime */
        $parserAnime = $theTVDb->parseAnime($tvdbId);
        $this->log('Content has been parsed. Anime: ' . $parserAnime->getTitle() . ' Seasons: ' . count($parserAnime->getSeasons()));

        /** @var AnimesAPI $animeApi */
        $animeApi = $doctrine->getRepository('LoopAnime\ShowsAPIBundle\Entity\AnimesAPI')->findOneBy(['apiAnimeKey' => $tvdbId]);
        if (!$animeApi) {
            $this->log("Anime doesn't exists, creating new anime!", 'comment');
            $command = new CreateAnime($parserAnime, $this->output);
        } else {
            $this->log("Anime already exists. Updating anime key " . $animeApi->getIdAnime() . "!", 'comment');
            $anime = $doctrine->getRepository('LoopAnimeShowsBundle:Animes')->find($animeApi->getIdAnime());
            $command = new EditAnime($anime, $parserAnime, $this->output);
        }

        $this->getContainer()->get('command_bus')->handle($command);
        $this->log('Anime inserted/updated successfully!', 'info');

        return true;
    }

    public function validate()
    {
        $keys = [];
        if (empty($data['tvdbId'])) {
            $keys[] = 'tvdbId';
        }
        if (!empty($keys)) {
            throw new \Exception('Data on the job is missing or incomplete. Expecting the follow keys:' . implode(", ", $keys));
        }
        return true;
    }

    public function getQueueType()
    {
        return QueueType::ADD_SERIE;
    }
}
