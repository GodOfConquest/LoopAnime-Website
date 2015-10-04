<?php

namespace LoopAnime\ShowsBundle\Controller;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CategoriesController extends Controller
{

    public function listCategoriesAction(Request $request)
    {
        $genresResults = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\Animes')->getGenres(false);
        $genres = [];
        foreach ($genresResults as $result) {
            $genres = array_unique(array_merge($genres, explode(",", $result["genres"])));
        }

        $notIn = "0";
        $categories = [];
        foreach ($genres as $genre) {
            if (empty($genre)) continue;

            /** @var Animes $animesByGenres */
            $animesByGenres = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\Animes')->getAnimesByGenres($genre, $notIn, false);
            $numAnimes = count($animesByGenres);

            if ($numAnimes > 0) {
                $animes = $animesByGenres[0];
            } else {
                /** @var Animes $animesByGenres */
                $animesByGenres = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\Animes')->getAnimesByGenres($genre, '0', false);
                $numAnimes = count($animesByGenres);
            }
            $notIn .= ", " . $animes->getId();

            $categories[] = array(
                "genre" 	=> $genre,
                "poster" 	=> $animes->getPoster(),
                "numAnimes" => $numAnimes
            );
        }

        return $this->render("LoopAnimeShowsBundle:categories:index.html.twig", array("categories" => $categories));
    }

    public function getCategoryAction($category, Request $request)
    {

        /** @var AnimesRepository $animesRepo */
        $animesRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\Animes');
        $query = $animesRepo->getAnimesByGenres($category, "");

        /** @var SlidingPagination $animes */
        $paginator = $this->get('knp_paginator');
        $animes = $paginator->paginate(
            $query,
            $request->query->get('page', 1),
            $request->query->get('maxr', 10)
        );

        if ($request->getRequestFormat() === "json") {
            $data = [];
            foreach ($animes as $anime) {
                $data["payload"]["categories"]["animes"][] = $anime->convert2Array();
            }
            return new JsonResponse($data);
        }
        return $this->render("LoopAnimeShowsBundle:categories:categoryInfo.html.twig", array("animes" => $animes, "category" => $category));
    }

}
