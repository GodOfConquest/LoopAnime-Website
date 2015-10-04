<?php

namespace LoopAnime\AppBundle\Sync\Handler;

use LoopAnime\AppBundle\Sync\Enum\SyncEnum;
use LoopAnime\AppBundle\Sync\Handler\Exception\ApiFaultException;
use LoopAnime\ShowsAPIBundle\Entity\AnimesAPI;
use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodesRepository;
use LoopAnime\ShowsBundle\Entity\AnimesSeasons;
use LoopAnime\ShowsBundle\Entity\ViewsRepository;
use LoopAnime\UsersBundle\Entity\Users;
use LoopAnime\UsersBundle\Entity\UsersFavoritesRepository;

class TraktTvHandler extends AbstractHandler {

    const SYNC_URL = "https://api-v2launch.trakt.tv/";

    public function markAsSeenEpisode(AnimesEpisodes $episode, Users $user)
    {
        /** @var AnimesSeasons $season */
        $season = $this->em->getRepository('LoopAnime\ShowsBundle\Entity\AnimesSeasons')->find($episode->getSeason());
        /** @var AnimesAPI $animeAPI */
        $animeAPI = $this->em->getRepository('LoopAnime\ShowsAPIBundle\Entity\AnimesAPI')->findOneBy(['idAnime' => $season->getAnime()]);
        /** @var Animes $anime */
        $anime = $this->em->getRepository('LoopAnime\ShowsBundle\Entity\Animes')->findOneBy(['id' => $season->getAnime()]);

        $POST = [
            "shows" => [
                [
                    "title" => $anime->getTitle(),
                    "ids" => [
                        "tvdb" => $animeAPI->getApiAnimeKey(),
                    ],
                    "seasons" => [
                        [
                            "number" => $season->getSeason(),
                            "episodes" => [
                                ["number" => $episode->getEpisode()]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->callCurl($this->getMarkEpisodeSeenApiUrl(), $POST, $user);
        return true;
    }

    public function importSeenEpisodes(Users $user)
    {
        set_time_limit(0);
        $animeAPIRepo = $this->em->getRepository('LoopAnime\ShowsAPIBundle\Entity\AnimesAPI');
        $seasonsRepo = $this->em->getRepository('LoopAnime\ShowsBundle\Entity\AnimesSeasons');
        /** @var UsersFavoritesRepository $usersFavRepo */
        $usersFavRepo = $this->em->getRepository('LoopAnime\UsersBundle\Entity\UsersFavorites');
        /** @var AnimesEpisodesRepository $episodeRepo */
        $episodeRepo = $this->em->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes');
        /** @var ViewsRepository $viewsRepo */
        $viewsRepo = $this->em->getRepository('LoopAnime\ShowsBundle\Entity\Views');

        $return = $this->callCurl($this->getImportApiUrl(), null, $user);
        foreach ($return as $anime) {
            if (empty($anime['show']['ids']['tvdb']))
                continue;
            /** @var AnimesAPI $animeObj */
            $allAnimes = $animeAPIRepo->findAll();
            $animeObj = $animeAPIRepo->findOneBy(['apiAnimeKey' => $anime['show']['ids']['tvdb']]);
            if ($animeObj) {
                foreach ($anime['seasons'] as $season) {
                    foreach ($season['episodes'] as $episode) {
                        $seasonObj = $seasonsRepo->findOneBy(['anime' => $animeObj->getIdAnime(), 'season' => $season['number']]);
                        $episode = $episodeRepo->getEpisodesBySeason($seasonObj->getId(), true, $episode['number']);
                        if (empty($episode))
                            continue;

                        $episode = $episode[0][0];
                        if (!$viewsRepo->isEpisodeSeen($user, $episode->getId()))
                            $viewsRepo->setEpisodeAsSeen($user, $episode->getId(), 0);
                    }
                }

                // Adds the anime to the favorite if its not
                if (!$usersFavRepo->isAnimeFavorite($user, $animeObj->getIdAnime()))
                    $usersFavRepo->setAnimeAsFavorite($user, $animeObj->getIdAnime());
            }
        }
        return true;
    }

    protected function callCurl($url, array $POST = null, Users $user)
    {
        $header = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $user->getTraktAccessToken(),
            'trakt-api-version: 2',
            'trakt-api-key: ' . $this->apiKey
        ];
        $ch = curl_init(self::SYNC_URL . $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (!empty($POST)) {
            $POST = json_encode($POST);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
        }
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode !== 200 && $httpcode !== 201) {
            error_log("[Error][Trakt]" . $url);
            error_log("[Error][Trakt]" . json_encode($header));
            error_log("[Error][Trakt]" . $result);
            throw new ApiFaultException("Trakt response header ", $httpcode . " Result: $result");
        }
        $result = json_decode($result, true);
        return $result;
    }

    protected function getUserApiUrl()
    {
        return "users/settings";
    }

    protected function getImportApiUrl()
    {
        return "sync/watched/shows";
    }

    protected function getMarkEpisodeSeenApiUrl()
    {
        return "sync/collection";
    }

    public function getName()
    {
        return SyncEnum::SYNC_TRAKT;
    }
}
