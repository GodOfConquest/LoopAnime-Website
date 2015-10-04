<?php

namespace LoopAnime\AppBundle\Sync\Handler;

use LoopAnime\AppBundle\Sync\Enum\SyncEnum;
use LoopAnime\AppBundle\Sync\Handler\Exception\ApiFaultException;
use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesRepository;
use LoopAnime\UsersBundle\Entity\Users;
use LoopAnime\UsersBundle\Entity\UsersFavoritesRepository;

class MyAnimeListHandler extends AbstractHandler {

    const SYNC_URL = "http://myanimelist.net/";

    public function markAsSeenEpisode(AnimesEpisodes $episode, Users $user)
    {
        $season = $this->em->getRepository('LoopAnime\ShowsBundle\Entity\AnimesSeasons')->find($episode->getSeason());
        $animeAPI = $this->em->getRepository('LoopAnime\ShowsAPIBundle\Entity\AnimesAPI')->findOneBy(['idAnime' => $season->getAnime()]);
        $anime = $this->em->getRepository('LoopAnime\ShowsBundle\Entity\Animes')->findOneBy(['id' => $season->getAnime()]);

        $POST = [
            "username" => $user->getMALUsername(),
            "password" => $user->getMALPassword(),
            "tvdb_id"  => $animeAPI->getApiAnimeKey(),
            "title"    => $anime->getTitle(),
            "year"     => date("Y", strtotime($anime->getStartTime())),
            "episodes" => [
                "season" => $season->getSeason(),
                "episode" => $episode->getEpisode(),
                "last_played" => new \DateTime("now"),
            ]
        ];
        $this->callCurl($this->getMarkEpisodeSeenApiUrl(), $POST, $user);
        return true;
    }

    public function importSeenEpisodes(Users $user)
    {
        /** @var AnimesRepository $animesRepo */
        $animesRepo = $this->em->getRepository('LoopAnime\ShowsBundle\Entity\Animes');
        /** @var UsersFavoritesRepository $usersFavRepo */
        $usersFavRepo = $this->em->getRepository('LoopAnime\UsersBundle\Entity\UsersFavorites');

        $return = $this->callCurl($this->getImportApiUrl(), null, $user);
        foreach ($return->anime as $anime) {
            /** @var Animes $animeObj */
            $animeObj = $animesRepo->findOneBy(['title' => $anime->series_title]);
            if ($animeObj !== null) {
                $myWatchedEpisodes = $anime->my_watched_episodes;
                $this->em->getRepository('LoopAnime\ShowsBundle\Entity\Views')->setEpisodesAsSeenBulk($myWatchedEpisodes, $user, $animeObj);

                // Adds the anime to the favorite if its not
                if (!$usersFavRepo->isAnimeFavorite($user, $animeObj->getId()))
                    $usersFavRepo->setAnimeAsFavorite($user, $animeObj->getId());
            }
        }
        return true;
    }

    /**
     * @param string $url
     * @param array $POST
     * @param Users $user
     * @return string
     * @throws ApiFaultException
     */
    protected function callCurl($url, array $POST = null, Users $user)
    {
        $url = $this->prepareLink($url, $user);
        $ch = curl_init(self::SYNC_URL . $url);
        curl_setopt($ch, CURLOPT_USERPWD, $user->getMALUsername() . ":" . $user->getMALPassword());
        curl_setopt($ch, CURLOPT_USERAGENT, $this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (!empty($POST)) {
            $POST = json_encode($POST);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($POST))
            );
        }
        $result = curl_exec($ch);
        $resultStatus = curl_getinfo($ch);
        if (empty($resultStatus['http_code']) || $resultStatus['http_code'] != "200" || $result === "Invalid credentials")
            throw new ApiFaultException('MyAnimeList', $result);

        $result = simplexml_load_string($result);
        if (!empty($result->error)) {
            throw new ApiFaultException('MyAnimeList', $result->error);
        }
        return $result;
    }

    private function prepareLink($link, Users $user)
    {
        return str_replace('|username|', $user->getMALUsername(), $link);
    }

    protected function getMarkEpisodeSeenApiUrl()
    {
        return "show/episode/seen/" . $this->apiKey;
    }

    protected function getUserApiUrl()
    {
        return "api/account/verify_credentials.json";
    }

    protected function getImportApiUrl()
    {
        return "malappinfo.php?u=|username|&status=all&type=anime";
    }

    public function getName()
    {
        return SyncEnum::SYNC_MAL;
    }
}
