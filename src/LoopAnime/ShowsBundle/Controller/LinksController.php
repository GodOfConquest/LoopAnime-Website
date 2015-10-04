<?php

namespace LoopAnime\ShowsBundle\Controller;

use Knp\Component\Pager\Paginator;
use LoopAnime\ShowsBundle\Entity\AnimesLinks;
use LoopAnime\ShowsBundle\Entity\AnimesLinksRepository;
use LoopAnime\ShowsBundle\Services\VideoService;
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

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $links = $paginator->paginate(
            $query,
            $request->query->get('page', 1),
            $request->query->get('maxr', 10)
        );

        if ($request->getRequestFormat() === "json") {
            $data = [];
            foreach ($links as $linkInfo) {
                $data["payload"]["links"][] = $linkInfo->convert2Array();
            }
            return new JsonResponse($data);
        }
        return $this->render("LoopAnimeShowsBundle:animes:episodeMirrors.html.twig", array("mirrors" => $links));
    }

    public function getDirectLinkAction($idLink, Request $request)
    {

        $linksRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesLinks');

        /** @var AnimesLinks $links */
        $links = $linksRepo->find($idLink);

        $videoService = new VideoService();
        $directLink = $videoService->getDirectVideoLink($links);

        $data["payload"]["links"][] = array_merge(['direct_link' => $directLink], $links->convert2Array());

        return new JsonResponse($data);

    }

}
