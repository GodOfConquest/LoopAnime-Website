<?php

namespace LoopAnime\AppBundle\BusCommand\Anime\Handler;

use Doctrine\ORM\EntityManager;
use LoopAnime\AppBundle\BusCommand\Anime\Exception\InvalidAnimeException;
use LoopAnime\AppBundle\BusCommand\Anime\Exception\InvalidEpisodeException;
use LoopAnime\AppBundle\BusCommand\Anime\Exception\InvalidSeasonException;
use LoopAnime\AppBundle\Parser\ParserAnime;
use LoopAnime\AppBundle\Parser\ParserEpisode;
use LoopAnime\AppBundle\Parser\ParserSeason;
use LoopAnime\AppBundle\BusCommand\Anime\EditAnime;
use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesSeasons;
use SimpleBus\Message\Handler\MessageHandler;
use SimpleBus\Message\Message;
use Symfony\Component\Console\Output\OutputInterface;

class EditAnimeCommandHandler implements MessageHandler {

    private $em;
    /** @var OutputInterface */
    private $output;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Handles the given message.
     *
     * @param Message|EditAnime $message
     * @return void
     */
    public function handle(Message $message)
    {
        /** @var ParserAnime $parserAnime */
        $parserAnime = $message->parserAnime;
        $this->validate($message);

        /** @var Animes $animeObj */
        $animeObj = $message->anime;
        $this->output = $message->output;
        $this->editAnime($animeObj, $parserAnime);
        foreach ($parserAnime->getSeasons() as $season) {
            $seasonObj = $this->editSeason($season, $animeObj);
            foreach ($season->getEpisodes() as $episode) {
                $this->insertEpisode($episode, $seasonObj);
            }
        }
    }

    private function editAnime(Animes $anime, ParserAnime $parserAnime)
    {
        if (!$anime) {
            $anime = new Animes();
            $this->output->writeln('<info>Anime didnt exist -- Creating a new one</info>');
        }
        $anime->setTitle($parserAnime->getTitle());
        $anime->setThemes($parserAnime->getThemes());
        $anime->setTypeSeries('anime');
        $anime->setEndTime($parserAnime->getEndTime());
        $anime->setStartTime($parserAnime->getStartTime());
        $anime->setStatus($parserAnime->getStatus());
        $anime->setGenres($parserAnime->getGenres());
        $anime->setPlotSummary($parserAnime->getSummary());
        $anime->setRunningTime($parserAnime->getRunningTime());
        $anime->setPoster($parserAnime->getPoster());
        $anime->setRating($parserAnime->getRating());
        $anime->setRatingCount($parserAnime->getRatingCount());
        $anime->setImdbId($parserAnime->getImdbId());

        $this->em->persist($anime);
        $this->em->flush();
        $this->output->writeln('Anime inserted/updated successfully!');
        return $anime;
    }

    private function editSeason(ParserSeason $parserSeason, Animes $anime)
    {
        $season = $this->em->getRepository('LoopAnimeShowsBundle:AnimesSeasons')->findOneBy(['anime' => $anime->getId(), 'season' => $parserSeason->getNumber()]);
        if (!$season) {
            $season = new AnimesSeasons();
            $this->output->writeln('<info>Season dont exist -- Anime: ' . $anime->getTitle() . ' Season: ' . $parserSeason->getNumber() . '</info>');
        }

        $season->setCreateTime(new \DateTime('now'));
        $season->setAnime($anime);
        $season->setNumberEpisodes($parserSeason->getTotalEpisodes());
        $season->setSeasonTitle($parserSeason->getTitle());
        $season->setSeason($parserSeason->getNumber());
        $season->setPoster($parserSeason->getPoster());

        $this->em->persist($season);
        $this->em->flush();
        $this->output->writeln('Season ' . $season->getId() . ' season: ' . $season->getSeason() . ' has been updated/inserted');
        return $season;
    }

    private function insertEpisode(ParserEpisode $parserEpisode, AnimesSeasons $season)
    {
        $episode = $this->em->getRepository('LoopAnimeShowsBundle:AnimesEpisodes')->findOneBy(['season' => $season->getId(), 'episode' => $parserEpisode->getEpisodeNumber()]);
        $operation = "updated";
        if (!$episode) {
            $episode = new AnimesEpisodes();
            $operation = "inserted";
            $this->output->writeln('<info>Episode does not exist - Season: ' . $season->getId() . ' Number: ' . $parserEpisode->getEpisodeNumber() . '</info>');
        }

        $episode->setPoster($parserEpisode->getPoster());
        $episode->setAbsoluteNumber($parserEpisode->getAbsoluteNumber());
        $episode->setAirDate(new \DateTime($parserEpisode->getAirDate()));
        $episode->setComments($parserEpisode->getComments());
        $episode->setSummary($parserEpisode->getSummary());
        $episode->setViews($parserEpisode->getViews());
        $episode->setImdbId($parserEpisode->getImdbId());
        $episode->setEpisode($parserEpisode->getEpisodeNumber());
        $episode->setEpisodeTitle($parserEpisode->getEpisodeTitle());
        $episode->setSeason($season);

        $this->em->persist($episode);
        $this->em->flush();
        $this->output->writeln('Episode ' . $episode->getId() . ' title: ' . $episode->getEpisodeTitle() . ' number: ' . $episode->getEpisode() . ' has been ' . $operation);
        return $episode;
    }

    private function validate(EditAnime $message)
    {
        $parserAnime = $message->parserAnime;
        if (empty($parserAnime->getTitle())) {
            throw new InvalidAnimeException('Anime needs to have a Title!');
        }
        if (empty($parserAnime->getPoster())) {
            throw new InvalidAnimeException('Anime needs to have a Poster!');
        }
        foreach ($parserAnime->getSeasons() as $season) {
            if (empty($season->getNumber()) && $season->getNumber() != 0) {
                throw new InvalidSeasonException('Season needs to have a Number, season: ' . $season->getNumber());
            }
            foreach ($season->getEpisodes() as $episode) {
                if (empty($episode->getEpisodeTitle())) {
                    throw new InvalidEpisodeException('Episode needs to have a title');
                }
                if (empty($episode->getEpisodeNumber())) {
                    throw new InvalidEpisodeException('Episode needs to have a number');
                }
            }
        }
    }

}
