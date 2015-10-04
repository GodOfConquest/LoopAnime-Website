<?php

namespace LoopAnime\ShowsBundle\Services;

use Doctrine\ORM\EntityManager;
use LoopAnime\AppBundle\Services\AbstractService;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodesRepository;
use LoopAnime\ShowsBundle\Entity\AnimesLinks;
use LoopAnime\ShowsBundle\Entity\ViewsRepository;
use LoopAnime\ShowsBundle\Event\EpisodeSeenEvent;
use LoopAnime\ShowsBundle\ShowEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\SecurityContext;

class EpisodeService extends AbstractService
{
    /** @var ViewsRepository */
    private $viewsRepo;
    /** @var AnimesEpisodesRepository  */
    private $episodeRepo;

    public function __construct(EntityManager $em, SecurityContext $securityContext, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->user = $securityContext->getToken()->getUser();
        $this->eventDispatcher = $eventDispatcher;

        $this->viewsRepo = $this->em->getRepository('LoopAnimeShowsBundle:Views');
        $this->episodeRepo = $this->em->getRepository('LoopAnimeShowsBundle:AnimesEpisodes');
    }

    public function getEpisode($idEpisode)
    {
        return $this->episodeRepo->find($idEpisode);
    }

    public function markEpisodeAsSeen(AnimesEpisodes $episode, AnimesLinks $link = null)
    {
        $idLink = $link ? $link->getId() : null;
        if ($this->viewsRepo->setEpisodeAsSeen($this->user, $episode->getId(), $idLink)) {
            $event = new EpisodeSeenEvent($this->user, $episode, $link);
            $this->eventDispatcher->dispatch(ShowEvents::EPISODE_SEEN, $event);
            return true;
        }
        return false;
    }

    public function markEpisodeAsUnseen(AnimesEpisodes $episode, AnimesLinks $link = null)
    {
        $idLink = $link ? $link->getId() : null;
        if ($this->viewsRepo->setEpisodeAsUnseen($this->user, $episode->getId(), $idLink)) {
            $event = new EpisodeSeenEvent($this->user, $episode, $link);
            $this->eventDispatcher->dispatch(ShowEvents::EPISODE_SEEN, $event);
            return true;
        }
        return false;
    }

    public function rateEpisode(AnimesEpisodes $episode, $ratingUp)
    {
        if ($this->episodeRepo->setRatingOnEpisode($episode, $ratingUp)) {
            return true;
        }
        return false;
    }

}
