<?php

namespace LoopAnime\ShowsBundle\Controller;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use LoopAnime\CommentsBundle\Entity\CommentsRepository;
use LoopAnime\CommentsBundle\Services\CommentsService;
use LoopAnime\ShowsAPIBundle\Services\SyncAPI\TraktTV;
use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodesRepository;
use LoopAnime\ShowsBundle\Entity\AnimesLinks;
use LoopAnime\ShowsBundle\Entity\AnimesLinksRepository;
use LoopAnime\ShowsBundle\Entity\AnimesRepository;
use LoopAnime\ShowsBundle\Entity\AnimesSeasons;
use LoopAnime\ShowsBundle\Entity\AnimesSeasonsRepository;
use LoopAnime\ShowsBundle\Entity\ViewsRepository;
use LoopAnime\ShowsBundle\Form\Type\CommentType;
use LoopAnime\ShowsBundle\Services\VideoService;
use LoopAnime\UsersBundle\Entity\Users;
use LoopAnime\UsersBundle\Entity\UsersFavoritesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EpisodesController extends Controller
{

    public function listEpisodesAction(Request $request)
    {
        /** @var AnimesEpisodesRepository $episodesRepo */
        $episodesRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes');

        if (!$request->get("anime") && !$request->get("season")) {
            $data['error'] = true;
            $data['error_msg'] = "Controller needs to have a valid anime and season";
            return new JsonResponse($data);
        }

        /** @var AnimesEpisodes[] $episodes */
        $episodes = null;
        if ($request->get("anime")) {
            $episodes = $episodesRepo->getEpisodesByAnime($request->get("anime"), false);
        } elseif ($request->get("season")) {
            $episodes = $episodesRepo->getEpisodesBySeason($request->get("season"), false);
        }

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $episodes = $paginator->paginate(
            $episodes,
            $request->query->get('page', 1),
            $request->query->get('maxr', 10)
        );

        if ($request->getRequestFormat() === "json") {
            $data = [];
            foreach ($episodes as $episodeInfo) {
                $extraMerge = ['anime' => ['id' => $episodeInfo['id'], 'title' => $episodeInfo['title']],
                    'season' => ['id' => $episodeInfo[0]->getIdSeason(), 'season' => $episodeInfo['season']]];
                $data["payload"]["episodes"][] = array_merge($extraMerge,$episodeInfo[0]->convert2Array());
            }
            return new JsonResponse($data);
        }
        return $this->render("LoopAnimeShowsBundle:Animes:episodesList.html.twig", array("episodes" => $episodes));
    }

    public function getEpisodeAction($idEpisode, Request $request)
    {
        $selLink = 0;
        if ($request->get("selLink")) {
            $selLink = $request->get("selLink");
        }
        /** @var CommentsRepository $commentsRepo */
        $commentsRepo = $this->getDoctrine()->getRepository('LoopAnimeCommentsBundle:Comments');
        /** @var AnimesEpisodesRepository $episodesRepo */
        $episodesRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes');

        /** @var AnimesEpisodes $episode */
        $episode = $episodesRepo->find($idEpisode);

        if ($episode === null) {
            return new JsonResponse(['error' => true, 'error_msg' => "Get parameter episode needs to be set and not empty."]);
        }

        $episode->setViews($episode->getViews() + 1);
        $this->getDoctrine()->getManager()->persist($episode);
        $this->getDoctrine()->getManager()->flush();

        $renderData = [];
        $renderData['episode'] = $episode;
        $renderData['selLink'] = $selLink;

        /** @var AnimesRepository $animesRepo */
        $animesRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:Animes');
        $renderData['anime'] = $animesRepo->getAnimeByEpisode($episode->getId(), false);

        /** @var AnimesSeasonsRepository $seasonsRepo */
        $seasonsRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesSeasons');
        $renderData['season'] = $seasonsRepo->getSeasonById($episode->getIdSeason(), true);

        /** @var AnimesLinksRepository $linksRepo */
        $linksRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesLinks');
        $renderData['links'] = $linksRepo->getLinksByEpisode($episode->getId());

        if(isset($renderData['links'][$selLink])) {
            $videoService = new VideoService();
            $directLink = $videoService->getDirectVideoLink($renderData['links'][$selLink]);
            $renderData['initialLink'] = $directLink;
        }

        $renderData['isIframe'] = false;

        /** @var ViewsRepository $viewsRepo */
        $viewsRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:Views');
        $renderData['isSeen'] = $viewsRepo->isEpisodeSeen($this->getUser(),$idEpisode);

        /** @var UsersFavoritesRepository $usersFavoritesRepo */
        $usersFavoritesRepo = $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:UsersFavorites');
        $renderData['isFavorite'] = $usersFavoritesRepo->isAnimeFavorite($this->getUser(),$renderData['anime']->getId());

        // Next Episode
        if ($nextEpisode = $episodesRepo->getNavigateEpisode($episode->getId())) {
            $renderData['nextEpisode'] = $nextEpisode;
        }
        // Prev Episode
        if ($prevEpisode = $episodesRepo->getNavigateEpisode($episode->getId(), false)) {
            $renderData['prevEpisode'] = $prevEpisode;
        }

        $renderData['comments'] = $commentsRepo->getCommentsByEpisode($episode, true);
        $renderData['totalFavorites'] = $episode->getRatingUp();

        // todo: put this form on the container -- Associate the Comment Handler
        //$form = $this->createForm(new CommentType($this->getDoctrine()->getManager()), $this->getUser());
        //$form->handleRequest($request);

        if ($request->getRequestFormat() === "html") {
            return $this->render(
                "LoopAnimeShowsBundle:Animes:episode.html.twig",
                $renderData);
        } elseif ($request->getRequestFormat() === "json") {
            $extraMerge = ['anime' => ['id' => $renderData['anime']->getId(), 'title' => $renderData['anime']->getTitle()],
                'season' => ['id' => $renderData['season']->getId(), 'season' => $renderData['season']->getSeason()]];
            $data["payload"]["episodes"][] = array_merge($extraMerge,$episode->convert2Array());
            return new JsonResponse($data);
        }

    }

    public function getEpisodesAction(Request $request)
    {
        /** @var Users $user */
        $user = $this->getUser();

        $typeEpisode = $request->get("typeEpisode", "recent");

        /** @var AnimesEpisodesRepository $animesEpisodes */
        $animesEpisodes = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesEpisodes');

        switch ($typeEpisode) {
            case "recent":
                $dql = $animesEpisodes->getRecentEpisodes(false);
                break;
            case "mostview":
                $dql = $animesEpisodes->getMostViewsEpisodes(false);
                break;
            case "mostrated":
                $dql = $animesEpisodes->getMostRatedEpisodes(false);
                break;
            case "userRecent":
                if (!$user) {
                    throw new \Exception("You need to be logged to see this content");
                }
                /** @var AnimesEpisodesRepository $animesEpisodesRepo */
                $animesEpisodesRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesEpisodes');
                $dql = $animesEpisodesRepo->getUserRecentsEpisodes($this->getUser(),false);
                break;
            case "userFuture":
                if (!$user) {
                    throw new \Exception("You need to be logged to see this content");
                }
                /** @var AnimesEpisodesRepository $animesEpisodesRepo */
                $animesEpisodesRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesEpisodes');
                $dql = $animesEpisodesRepo->getUserFutureEpisodes($this->getUser(),false);
                break;
            case "userHistory":
                if (!$user) {
                    throw new \Exception("You need to be logged to see this content");
                }
                /** @var AnimesEpisodesRepository $animesEpisodesRepo */
                $animesEpisodesRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesEpisodes');
                $dql = $animesEpisodesRepo->getUserHistoryEpisodes($this->getUser(),false);
                break;
        }

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        /** @var SlidingPagination $episodes */
        $episodes = $paginator->paginate(
            $dql,
            $request->query->get('page', 1),
            $request->query->get('maxr', 20)
        );

        if (!$episodes->valid()) {
            return new JsonResponse(['error' => true, 'error_msg' => "No episodes found!"]);
        }

        $data = [];

        if ($request->getRequestFormat() === "json") {
            /** @var AnimesEpisodes[] $episodes */
            foreach ($episodes as $episode) {
                /** @var AnimesSeasons $animesSeasons */
                $animesSeasons = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesSeasons')->find($episode->getIdSeason());
                /** @var Animes $anime */
                $anime = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:Animes')->find($animesSeasons->getIdAnime());
                $extraMerge = ['anime' => ['id' => $anime->getId(), 'title' => $anime->getTitle()],
                    'season' => ['id' => $animesSeasons->getId(), 'season' => $animesSeasons->getSeason()]];
                $data["payload"]["episodes"][] = array_merge($extraMerge,$episode->convert2Array());
            }

            return new JsonResponse($data);
        }
        return $this->render("LoopAnimeShowsBundle:extra:videoGallery.html.twig", array("episodes" => $episodes));
    }

    public function markFavoriteAction(Request $request)
    {
        /** @var UsersFavoritesRepository $usersRepo */
        $usersRepo = $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:UsersFavorites');
        $renderData["title"] = "Operation - Mark as Favorite";
        if($usersRepo->setAnimeAsFavorite($this->getUser(), $request->get("id_anime"))) {
            $renderData["msg"] = "Anime was Marked/Dismarked as favorite.";
        } else {
            $renderData["msg"] = "Technical error - Please try again later.";
        }
        $renderData['closeButton'] = false;
        $renderData['buttons'][] = array("text"=>"Close", "js"=>"onclick=".'"'."$('#myModal').remove();$('.modal-backdrop').remove()".'"', "class"=>"btn-primary");
        return new JsonResponse($renderData);
    }

    public function ajaxRequestAction(Request $request)
    {

        $url = $this->generateUrl('hwi_oauth_connect');

        /** @var Users $user */
        if(!$user = $this->getUser()) {
            $renderData["title"] = "Error - Login Required";
            $renderData["msg"] = "You need to login to use this feature.";
            $renderData['closeButton'] = false;
            $renderData["buttons"][] = array("text"=>"Close", "js"=>"onclick=".'"'."$('#myModal').remove();$('.modal-backdrop').remove()".'"', "class"=>"btn-primary");
            $renderData["buttons"][] = array("text"=>"Login", "js"=>"onclick=".'"'."window.location='".$url.'"', "class"=>"btn-primary");
        } else {
            $renderData = [];

            switch($request->get('op')) {
                case "mark_favorite":
                    /** @var UsersFavoritesRepository $usersRepo */
                    $usersRepo = $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:UsersFavorites');
                    $renderData["title"] = "Operation - Mark as Favorite";
                    if($usersRepo->setAnimeAsFavorite($this->getUser(), $request->get("id_anime"))) {
                        $renderData["msg"] = "Anime was Marked/Dismarked as favorite.";
                    } else {
                        $renderData["msg"] = "Technical error - Please try again later.";
                    }
                    break;
                case "set_progress":
                    /** @var ViewsRepository $viewsRepo */
                    $viewsRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:Views');
                    if($viewsRepo->setViewProgress($user, $request->get("id_episode"), $request->get('id_link'), $request->get('watched_time'))) {
                        $renderData["msg"] = "Progress has been saved";
                    } else {
                        $renderData["msg"] = "Progress could not be saved";
                    }
                    break;
                case "get_last_progress":
                    /** @var ViewsRepository $viewsRepo */
                    $viewsRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:Views');
                    if($data = $viewsRepo->getViewProgress($user, $request->get("id_episode"))) {
                        $renderData['data'] = $data;
                        $renderData["msg"] = "Last Progress retrieved";
                    } else {
                        $renderData["msg"] = "No Progress could be found";
                    }
                    break;
                case "mark_as_seen":
                    /** @var ViewsRepository $viewsRepo */
                    $viewsRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:Views');
                    $renderData["title"] = "Operation - Mark as (Un)Seen";
                    if($viewsRepo->setEpisodeAsSeen($user, $request->get("id_episode"), $request->get('id_link'))) {
                        $renderData["msg"] = "Episode marked as seen.";
                    } else {
                        $renderData["msg"] = "Technical error - Please try again later.";
                    }
                    if($user->getTraktUsername() != "") {
                        /** @var TraktTV $trakt */
                        $trakt = $this->get("sync.trakt");
                        $trakt->markAsSeenEpisode($this->getEpisodeObject($request->get("id_episode")));
                    }
                    break;
                case "comment_episode":
                    $renderData["title"] = "Operation - Mark as (Un)Seen";
                    $commentController = new CommentsService($this->getDoctrine()->getManager());
                    $user = $this->getUser();
                    /** @var AnimesEpisodes $episode */
                    $episode = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes')->find($request->get('id_episode'));
                    if($commentController->setCommentOnEpisode($episode, $user, $request->get('comment'))) {
                        $renderData["msg"] = "Comment has been created successfully!";
                    } else {
                        $renderData["msg"] = "Technical error - Please try again later.";
                    }

                    break;
                case "rating":
                    $renderData["title"] = "Operation - Rating";
                    $ratingUp = ($request->get('ratingUp') ? true : false);
                    /** @var AnimesEpisodesRepository $episodesRepo */
                    $episodesRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes');
                    if($data = $episodesRepo->setRatingOnEpisode($user, $request->get("id_episode"), $request->get('ratingUp'))) {
                        $renderData["data"] = $data;
                        $renderData["msg"] = "Thank you for voting.";
                    } else {
                        $renderData["msg"] = "Technical error - Please try again later.";
                    }
                    break;
                default:
                    $renderData["title"] = "Operation Unknow";
                    $renderData["msg"] = "Technical error - Please try again later.";
                    break;
            }

        }
        $renderData['closeButton'] = false;
        $renderData['buttons'][] = array("text"=>"Close", "js"=>"onclick=".'"'."$('#myModal').remove();$('.modal-backdrop').remove()".'"', "class"=>"btn-primary");
        return new JsonResponse($renderData);
    }

    /**
     * @param $idEpisode
     * @return null|AnimesEpisodes
     */
    private function getEpisodeObject($idEpisode)
    {
        /** @var AnimesEpisodesRepository $episodesRepo */
        $episodesRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes');
        return $episodesRepo->find($idEpisode);
    }

}
