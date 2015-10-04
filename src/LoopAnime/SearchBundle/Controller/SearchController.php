<?php

namespace LoopAnime\SearchBundle\Controller;

use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodesRepository;
use LoopAnime\ShowsBundle\Entity\AnimesRepository;
use LoopAnime\UsersBundle\Entity\UsersFavoritesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{

    public function searchFormAction(Request $request)
    {
        return $this->redirect($this->generateUrl('loopanime_search_search', ['term' => $request->get('term')]));
    }

    public function searchAction($term, Request $request)
    {
        if (empty($term)) {
            $term = $request->get('q');
        }

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');

        /** @var AnimesRepository $animeRepo */
        $animeRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:Animes');
        $animesq = $animeRepo->getAnimesByTitle($term);
        /** @var SlidingPagination $animes */
        $animes = $paginator->paginate(
            $animesq,
            $request->query->get('page', 1),
            $request->query->get('maxr', 20)
        );

        /** @var AnimesEpisodesRepository $episodesRepo */
        $episodesRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesEpisodes');
        $episodesq = $episodesRepo->getEpisodesByTitle($term);

        /** @var SlidingPagination $episodes */
        $episodes = $paginator->paginate(
            $episodesq,
            $request->query->get('page2', 1),
            $request->query->get('maxr2', 20)
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

        return $this->render('LoopAnimeSearchBundle:Search:index.html.twig', [
            'animes' => $animes,
            'episodes' => $episodes,
            "searchTerm" => $term,
            'userFavorites' => $userFavorites
        ]);
    }

}