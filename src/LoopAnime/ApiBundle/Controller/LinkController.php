<?php

namespace LoopAnime\ApiBundle\Controller;

use FOS\RestBundle\View\View;
use LoopAnime\ApiBundle\Exceptions\ParameterRequiredException;
use LoopAnime\ApiBundle\Exceptions\ResourceNotFoundException;
use LoopAnime\ShowsBundle\Entity\AnimesLinks;
use LoopAnime\ShowsBundle\Entity\AnimesLinksRepository;
use LoopAnime\ShowsBundle\Services\VideoService;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LinkController extends BaseController
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @ApiDoc(
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when missing an argument",
     *  },
     *  resource=true,
     *  description="Return a list of resources",
     *  filters={
     *      {"name"="episode", "dataType"="integer", "requirement"="\d+", "description"="Id of resource", "required"="true"}
     *  },
     *  requirements={
     *      {"name"="access_token", "dataType"="string", "requirement"="ACCESS_TOKEN", "description"="Your access token", "required"="true"}
     * }
     * )
     */
    public function getLinksAction(Request $request)
    {
        if (empty($request->get('episode'))) {
            throw new ParameterRequiredException('episode');
        }

        /** @var AnimesLinksRepository $linksRepo */
        $linksRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:AnimesLinks');
        $links = $linksRepo->getLinksByEpisode($request->get('episode'));

        $view = $this->view($links, 200);
        return $this->handleView($view);
    }

    /**
     * @param AnimesLinks $link
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @ApiDoc(
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when resource was not found",
     *  },
     *  resource=true,
     *  description="Return a Link Resource - Also deep link it and gets a direct link that can be right away streamed",
     *  requirements={
     *      {"name"="link", "dataType"="integer", "requirement"="\d+", "description"="Id of resource", "required"="true"},
     *      {"name"="access_token", "dataType"="string", "requirement"="ACCESS_TOKEN", "description"="Your access token", "required"="true"}
     *  }
     * )
     */
    public function getLinkAction(AnimesLinks $link)
    {
        if(null === $link) {
            throw new ResourceNotFoundException($link);
        }

        /** @var VideoService $videoService */
        $videoService = $this->get('loopanime_video_service');
        $directLink = $videoService->getDirectVideoLink($link);
        /** @var View $view */
        $view = $this->view(['direct_link' => $directLink, $link], 200);
        return $this->handleView($view);
    }

}
