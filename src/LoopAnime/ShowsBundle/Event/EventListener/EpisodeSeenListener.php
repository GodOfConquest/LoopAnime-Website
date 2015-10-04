<?php

namespace LoopAnime\ShowsBundle\Event\EventListener;

use LoopAnime\AppBundle\Sync\Services\SyncService;
use LoopAnime\ShowsBundle\Event\EpisodeSeenEvent;

class EpisodeSeenListener
{

    /** @var SyncService */
    private $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function onEpisodeSeen(EpisodeSeenEvent $event)
    {
        $this->syncService->syncEpisodeSeen($event->getEpisode(), $event->getUser());
    }

}
