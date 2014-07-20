<?php

namespace LoopAnime\ShowsBundle\Controller;

use Knp\Component\Pager\Paginator;
use LoopAnime\ShowsBundle\Entity\AnimesLinks;
use LoopAnime\ShowsBundle\Entity\AnimesLinksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LinksController extends Controller
{

    public function getLinksAction(Request $request)
    {
        /** @var AnimesLinksRepository $linksRepo */
        $linksRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesLinks');

        if ($request->get("episode")) {
            $query = $linksRepo->getLinksByEpisode($request->get('episode'), false);
        } else {
            throw new \Exception("Episode ID is missing.");
        }

        if ($request->getRequestFormat() === "html") {

            /** @var Paginator $paginator */
            $paginator  = $this->get('knp_paginator');
            $links = $paginator->paginate(
                $query,
                $request->query->get('page', 1),
                10
            );

            if(!$links->valid()) {
                throw $this->createNotFoundException("No links were found to this episode, add a link here!");
            }

            $render = $this->render("LoopAnimeShowsBundle:animes:episodeMirrors.html.twig", array("mirrors" => $links));
            return $render;
        } elseif ($request->getRequestFormat() === "json") {

            /** @var AnimesLinks[] $links */
            $links = $query->getResult();

            $data = [];
            foreach ($links as $linkInfo) {
                $data["payload"]["episodes"][] = $linkInfo->convert2Array();
            }

            return new JsonResponse($data);
        }

    }

    public function getDirectLinkAction($idLink, Request $request)
    {

        $linksRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesLinks');

        /** @var AnimesLinks $links */
        $links = $linksRepo->find($idLink);

        if (empty($links)) {
            throw $this->createNotFoundException("The link id does not exists");
        }

        $data["payload"]["episodes"][] = $links->convert2Array();

        return new JsonResponse($data);

    }

}
