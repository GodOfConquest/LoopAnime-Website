<?php

namespace LoopAnime\ApiBundle\Controller;

use LoopAnime\ApiBundle\Exceptions\ResourceNotFoundException;
use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesRepository;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class AnimeController extends BaseController {

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @ApiDoc(
     *  statusCodes={
     *      200="Returned when successful"
     *  },
     *  resource=true,
     *  description="Return list of resources",
     *  requirements={
     *      {"name"="access_token", "dataType"="string", "requirement"="ACCESS_TOKEN", "description"="Your access token", "required"="true"}
     *  }
     * )
     */
    public function getAnimesAction(Request $request)
    {
        /** @var AnimesRepository $animesRepo */
        $animesRepo = $this->getDoctrine()->getRepository('LoopAnimeShowsBundle:Animes');
        $payload = $this->paginateObject($request, $animesRepo, []);

        $view = $this->view($payload, 200);
        return $this->handleView($view);
    }

    /**
     * @param Animes $anime
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @ApiDoc(
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when Resource could not be found",
     *  },
     *  resource=true,
     *  description="Return one resource",
     *  requirements={
     *      {"name"="anime", "dataType"="integer", "requirement"="\d+", "description"="Id of resource"},
     *      {"name"="access_token", "dataType"="string", "requirement"="ACCESS_TOKEN", "description"="Your access token", "required"="true"}
     *  }
     * )
     */
    public function getAnimeAction(Animes $anime)
    {
        if ($anime === null) {
            throw new ResourceNotFoundException($anime);
        }
        $view = $this->view($anime, 200);
        return $this->handleView($view);
    }

}
