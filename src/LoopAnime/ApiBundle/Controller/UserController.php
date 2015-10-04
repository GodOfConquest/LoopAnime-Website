<?php

namespace LoopAnime\ApiBundle\Controller;

use LoopAnime\ApiBundle\Exceptions\ResourceNotFoundException;
use LoopAnime\UsersBundle\Entity\Users;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @ApiDoc(
     *  statusCodes={
     *      200="Returned when successful"
     *  },
     *  resource=true,
     *  description="Return a list of resources",
     *  requirements={
     *      {"name"="access_token", "dataType"="string", "requirement"="ACCESS_TOKEN", "description"="Your access token", "required"="true"}
     *  }
     * )
     */
    public function getUsersAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:Users');
        $payload = $this->paginateObject($request, $repository, []);

        $view = $this->view($payload, 200);
        return $this->handleView($view);
    }

    /**
     * @param Users $user
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
     *      {"name"="user", "dataType"="integer", "requirement"="\d+", "description"="Id of resource"},
     *      {"name"="access_token", "dataType"="string", "requirement"="ACCESS_TOKEN", "description"="Your access token", "required"="true"}
     *  }
     * )
     */
    public function getUserAction(Users $user)
    {
        if (null === $user) {
            throw new ResourceNotFoundException($user);
        }
        $view = $this->view($user, 200);
        return $this->handleView($view);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @ApiDoc(
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when Resource could not be found",
     *  },
     *  resource=true,
     *  description="Return the user which is logged in",
     *  requirements={
     *      {"name"="access_token", "dataType"="string", "requirement"="ACCESS_TOKEN", "description"="Your access token", "required"="true"}
     *  }
     * )
     */
    public function getUserLoggedAction()
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new ResourceNotFoundException($user);
        }
        $view = $this->view($user, 200);
        return $this->handleView($view);
    }

}
