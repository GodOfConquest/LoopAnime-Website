<?php

namespace LoopAnime\ShowsBundle\Controller;

use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodesRepository;
use LoopAnime\ShowsBundle\Entity\AnimesRepository;
use LoopAnime\ShowsBundle\Entity\AnimesSeasonsRepository;
use LoopAnime\ShowsBundle\Entity\Views;
use LoopAnime\ShowsBundle\Entity\ViewsRepository;
use LoopAnime\UsersBundle\Entity\Users;
use LoopAnime\UsersBundle\Entity\UsersFavoritesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AnimesController extends Controller
{
    public function indexAction(Request $request)
    {
        /** @var AnimesRepository $animeRepo */
        $animeRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\Animes');
        /** @var AnimesEpisodesRepository $animesEpisodes */
        $animesEpisodes = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesEpisodes');
        /** @var UsersFavoritesRepository $userFavRepo */
        $userFavRepo = $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:UsersFavorites');

        $featuredAnimes = $animeRepo->getFeaturedAnimes();
        $favorites = [];
        foreach ($featuredAnimes as $anime) {
            $favorites[$anime->getId()] = $userFavRepo->isAnimeFavorite($this->getUser(), $anime->getId());
        }
        $query = $animesEpisodes->getRecentEpisodes(false);
        $paginator = $this->get('knp_paginator');
        $recentEpisodes = $paginator->paginate(
            $query,
            $request->query->get('page', 1),
            $request->query->get('maxr', 12)
        );

        return $this->render('LoopAnimeShowsBundle:index:index.html.twig', ['featuredAnimes' => $featuredAnimes, 'recentEpisodes' => $recentEpisodes, 'favorites' => $favorites]);
    }

    public function myAnimesAction(Request $request)
    {
        /** @var AnimesEpisodesRepository $episodesRepo */
        $episodesRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes');
        /** @var ViewsRepository $viewsRepo */
        $viewsRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\Views');
        /** @var UsersFavoritesRepository $userFavorites */
        $youWereWatching = $viewsRepo->getIncompleteViews($this->getuser());
        $userFavorites = $this->getDoctrine()->getRepository('LoopAnime\UsersBundle\Entity\UsersFavorites');
        /** @var Animes[] $animes */
        $animes = $userFavorites->getUsersFavoriteAnimes($this->getUser(), false);
        $watchNext = [];
        foreach ($animes as $anime) {
            $idEpisode = null;
            /** @var Views $lastView */
            $lastView = $viewsRepo->findOneBy(['idAnime' => $anime['idAnime'], 'completed' => 1], ['idEpisode' => 'DESC']);
            if (!$lastView) {
                $nextEpisode = $episodesRepo->getEpisodesByAnime($anime['idAnime']);
                if ($nextEpisode)
                    $nextEpisode = $nextEpisode[0];
            } else {
                $nextEpisode = $episodesRepo->getNavigateEpisode($lastView->getIdEpisode(), true);
            }
            if (!!$nextEpisode) {
                $watchNext[] = ['anime' => $anime, 'episode' => $nextEpisode];
            }
        }

        return $this->render('LoopAnimeShowsBundle:MyAnimes:index.html.twig', ['youWereWatching' => $youWereWatching, 'watchNext' => $watchNext]);
    }

    public function listAnimesAction(Request $request)
    {
        /** @var AnimesRepository $animesRepo */
        $animesRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\Animes');

        $query = "";

        $type = "ordered";
        if ($request->get("type")) {
            switch ($request->get("type")) {
                case "mostrated":
                    $type = "mostrated";
                    $query = $animesRepo->getAnimesMostRated($this->getUser());
                    break;
                case "recents":
                    $type = "recents";
                    $query = $animesRepo->getAnimesRecent();
                    break;
            }
        }

        if ($type === "ordered" && $request->get("title")) {
            $query = $animesRepo->getAnimesByTitle($request->get("title"));
        } elseif ($type === "ordered") {
            $query = $animesRepo->getAnimesByTitle("");
        }

        /** @var Animes[] $animes */
        $paginator = $this->get('knp_paginator');
        $animes = $paginator->paginate(
            $query,
            $request->query->get('page', 1),
            $request->query->get('maxr', 20)
        );

        $userFavorites = [];
        if ($this->getUser() !== null) {
            /** @var UsersFavoritesRepository $usersFavRepo */
            $usersFavRepo = $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:UsersFavorites');
            $userFavorites = $usersFavRepo->getUsersFavoriteAnimes($this->getUser());
            foreach ($userFavorites as &$val) {
                $val = $val['idAnime'];
            }
        }

        return $this->render("LoopAnimeShowsBundle:Animes:listAnimes.html.twig", array("pagination" => $animes, "type" => $type, "userFavorites" => $userFavorites));

    }

    public function getAnimeAction(Animes $idAnime)
    {
        /** @var AnimesEpisodesRepository $episodesRepo */
        $episodesRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesEpisodes');
        /** @var AnimesSeasonsRepository $seasonsRepo */
        $seasonsRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesSeasons');
        /** @var UsersFavoritesRepository $usersFavRepo */
        $usersFavRepo = $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:UsersFavorites');

        $latestEpisodes = $episodesRepo->getLatestEpisodes($idAnime, 4);
        $seasons = $seasonsRepo->getSeasonsByAnime($idAnime->getId(), true);
        $animeInformation = $this->getDoctrine()->getRepository("LoopAnimeShowsBundle:Animes")->getAnimeStats($idAnime);
        $isFavorite = $usersFavRepo->isAnimeFavorite($this->getUser(), $idAnime);

        return $this->render("LoopAnimeShowsBundle:Animes:anime.html.twig", ["anime" => $idAnime, 'latestEpisodes' => $latestEpisodes, 'seasons' => $seasons, 'animeInormation' => $animeInformation, 'isFavorite' => $isFavorite]);
    }

    public function ajaxRequestAction(Request $request)
    {
        $url = $this->generateUrl('hwi_oauth_connect');

        /** @var Users $user */
        if (!$user = $this->getUser()) {
            $renderData["title"] = "Error - Login Required";
            $renderData["msg"] = "You need to login to use this feature.";
            $renderData['closeButton'] = false;
            $renderData["buttons"][] = array("text"=>"Close", "js"=>"onclick=" . '"' . "$('#myModal').remove();$('.modal-backdrop').remove()" . '"', "class"=>"btn-primary");
            $renderData["buttons"][] = array("text"=>"Login", "js"=>"onclick=" . '"' . "window.location='" . $url . '"', "class"=>"btn-primary");
        } else {
            $renderData = [];

            switch ($request->get('op')) {
                case "mark_favorite":
                    if (empty($request->get("id_anime"))) {
                        throw new \Exception("id_anime is a required parameter.");
                    }
                    /** @var UsersFavoritesRepository $usersRepo */
                    $usersRepo = $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:UsersFavorites');
                    $renderData["title"] = "Operation - Mark as Favorite";
                    if ($usersRepo->setAnimeAsFavorite($this->getUser(), $request->get("id_anime"))) {
                        $renderData["msg"] = "Anime was Marked/Dismarked as favorite.";
                    } else {
                        $renderData["msg"] = "Technical error - Please try again later.";
                    }
                    break;
                case "rating":
                    $renderData["title"] = "Operation - Rating";
                    $ratingUp = ($request->get('ratingUp') ? true : false);
                    /** @var AnimesRepository $animesRepo */
                    $animesRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\Animes');
                    if ($data = $animesRepo->setRatingOnEpisode($user, $request->get("id_anime"), $request->get('ratingUp'))) {
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
        $renderData['buttons'][] = array("text"=>"Close", "js"=>"onclick=" . '"' . "$('#myModal').remove();$('.modal-backdrop').remove()" . '"', "class"=>"btn-primary");
        return new JsonResponse($renderData);
    }

}
