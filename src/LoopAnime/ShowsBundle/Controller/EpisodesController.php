<?php

namespace LoopAnime\ShowsBundle\Controller;

use LoopAnime\AppBundle\Crawler\Enum\VideoQualityEnum;
use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodesRepository;
use LoopAnime\ShowsBundle\Entity\AnimesLinks;
use LoopAnime\ShowsBundle\Entity\AnimesLinksRepository;
use LoopAnime\ShowsBundle\Entity\AnimesSeasons;
use LoopAnime\ShowsBundle\Entity\ViewsRepository;
use LoopAnime\ShowsBundle\Services\EpisodeService;
use LoopAnime\UsersBundle\Entity\Users;
use LoopAnime\UsersBundle\Entity\UsersFavoritesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EpisodesController extends Controller
{

    public function listEpisodesAction(Request $request)
    {
        /** @var AnimesEpisodesRepository $episodesRepo */
        $episodesRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes');

        if (!$request->get("anime") && !$request->get("season")) {
            return new JsonResponse(['isError' => true, 'errorMsg' => 'Controller needs to have a valid anime and season']);
        }

        $query = null;
        if ($request->get("anime")) {
            $query = $episodesRepo->getEpisodesByAnime($request->get("anime"), false);
        } elseif ($request->get("season")) {
            $query = $episodesRepo->getEpisodesBySeason($request->get("season"), false);
        }
        $episodes = $this->get('knp_paginator')->paginate($query, $request->query->get('page', 1), $request->query->get('maxr', 10));

        return $this->render("LoopAnimeShowsBundle:Animes:episodesList.html.twig", array("episodes" => $episodes));
    }

    public function getEpisodeAction(AnimesEpisodes $episode, $selLink, Request $request)
    {
        if ($episode === null) {
            return new JsonResponse(['isError' => true, 'errorMsg' => "Get parameter episode needs to be set and not empty."]);
        }
        $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesEpisodes')->incrementView($episode);
        $anime = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:Animes')->getAnimeByEpisode($episode->getId(), false);
        $season = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesSeasons')->getSeasonById($episode->getSeason(), true);
        $links = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesLinks')->getLinksByEpisode($episode->getId());

        $videoService = $this->get('loopanime_video_service');
        $isIframe = false;

        $playlist = [];
        if (isset($links[$selLink])) {
            $playlist = $videoService->getDirectVideoLink($links[$selLink]);
            if (empty($playlist)) {
                $isIframe = true;
                $playlist[VideoQualityEnum::DEFAULT_QUALITY][] = $videoService->getIframeLink($links[$selLink]);
            }
        }
        $renderData = [
            'episode' => $episode,
            'selLink' => $selLink,
            'season' => $season,
            'anime' => $anime,
            'links' => $links,
            'playlist' => $playlist,
            'isIframe' => $isIframe,
            'isSeen' => $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:Views')->isEpisodeSeen($this->getUser(), $episode->getId()),
            'isFavorite' => $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:UsersFavorites')->isAnimeFavorite($this->getUser(), $anime->getId()),
            'comments' => $this->getDoctrine()->getRepository('LoopAnimeCommentsBundle:Comments')->getCommentsByEpisode($episode, true),
            'totalFavorites' => $episode->getRatingUp()
        ];

        return $this->render("LoopAnimeShowsBundle:Animes:episode.html.twig", $renderData);
    }

    public function releaseDateAction(Request $request)
    {
        $date = new \DateTime($request->get('rd'));
        $prevDate = clone $date; $prevDate->modify('-1 day');
        $nextDate = clone $date; $nextDate->modify('+1 day');
        /** @var AnimesEpisodesRepository $animesEpisodes */
        $animesEpisodes = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesEpisodes');
        $episodes = $animesEpisodes->getEpisodesByDate($date, false);

        return $this->render('LoopAnimeShowsBundle:index:releaseSchedule.html.twig', [
            'prevDate' => $prevDate,
            'currDate' => $date,
            'nextDate' => $nextDate,
            'episodes' => $episodes
        ]);
    }

    public function navigateSeasonAction(Request $request)
    {
        $season = $request->get('season');
        $season = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesSeasons')->find($season);
        if (!$season) {
            throw new NotFoundHttpException();
        }
        $prevSeason = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesSeasons')->getSibling($season, (int)($season->getSeason() - 1));
        $nextSeason = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesSeasons')->getSibling($season, (int)($season->getSeason() + 1));
        $episodes = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesEpisodes')->getEpisodesBySeason($season, true);

        return $this->render('LoopAnimeShowsBundle:Animes:episodeSeasonsContainer.html.twig', ['prevSeason' => $prevSeason, 'nextSeason' => $nextSeason, 'episodes' => $episodes, 'season' => $season]);
    }

    public function downloadAction(AnimesEpisodes $episode, $selLink)
    {
        $videoService = $this->get('loopanime_video_service');

        $link = '';
        $links = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesLinks')->getLinksByEpisode($episode->getId());
        if (isset($links[$selLink])) {
            $link = $videoService->getDirectVideoLink($links[$selLink]);
        }

        $quoted = sprintf('"%s"', addcslashes(basename($episode->getEpisodeTitle()), '"\\'));

        // Generate response
        $response = new Response();

        // Set headers
        $response->setStatusCode(200);
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', "application/octet-stream");
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $quoted . '";');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');

        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(readfile($link));

        $response->send();
    }

    public function ajaxRequestAction(Request $request)
    {
        /** @var ViewsRepository $viewsRepo */
        $viewsRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:Views');
        /** @var UsersFavoritesRepository $usersRepo */
        $usersRepo = $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:UsersFavorites');
        /** @var AnimesLinksRepository $linksRepo */
        $linksRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesLinks');

        /** @var EpisodeService $episodeService */
        $episodeService = $this->get('loopanime.episode.service');

        /** @var Users $user */
        if (!$user = $this->getUser()) {
            return new JsonResponse(['isError' => true, 'error' => 'You need to be logged in to perform this actions']);
        }

        $data = [];
        $msg = "";
        switch ($request->get('op')) {
            case "mark_favorite":
                if ($usersRepo->setAnimeAsFavorite($this->getUser(), $request->get("id_anime"))) {
                                    $msg = "Anime was updated successfully";
                }
                break;
            case "set_progress":
                if ($viewsRepo->setViewProgress($user, $request->get("id_episode"), $request->get('id_link'), $request->get('watched_time'))) {
                                    $msg = "Progress has been set";
                }
                break;
            case "get_last_progress":
                if ($data = $viewsRepo->getViewProgress($user, $request->get("id_episode"))) {
                                    $msg = "Last Progress retrieved";
                } else {
                                    $msg = 'There is no record of you seing this episode';
                }
                break;
            case "mark_as_unseen":
            case "mark_as_seen":
                $episode = $episodeService->getEpisode($request->get('id_episode'));
                $link = null;
                if (!empty($request->get('id_link'))) {
                    $link = $linksRepo->find($request->get('id_link'));
                }
                if ($episode) {
                    if ($request->get('op') === "mark_as_seen") {
                        $episodeService->markEpisodeAsSeen($episode, $link);
                        $msg = "Episode marked as seen.";
                    } elseif ($request->get('op') === "mark_as_unseen") {
                        $episodeService->markEpisodeAsUnseen($episode, $link);
                        $msg = "Episode marked as unseen";
                    }
                }

                break;
            case "rating":
                if ($episodeService->rateEpisode($request->get("id_episode"), $request->get('ratingUp'))) {
                                    $msg = "Thank you for voting.";
                }
                break;
        }

        return new JsonResponse(['isError' => empty($msg) ? true : false, 'msg' => empty($msg) ? 'Technical Error with your request - try again latter.' : $msg, 'data' => $data]);
    }

}
