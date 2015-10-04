<?php

namespace LoopAnime\UsersBundle\Controller;

use LoopAnime\CommentsBundle\Entity\CommentsRepository;
use LoopAnime\UsersBundle\Entity\Users;
use LoopAnime\UsersBundle\Entity\UsersFavorites;
use LoopAnime\UsersBundle\Entity\UsersFavoritesRepository;
use LoopAnime\UsersBundle\Entity\UsersPreferences;
use LoopAnime\UsersBundle\Entity\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserActionsController extends Controller
{

    public function setPreferencesAction(Request $request)
    {
        /** @var Users $user */
        $user = $this->getUser();
        $user = $this->getDoctrine()->getRepository('LoopAnime\UsersBundle\Entity\Users')->find($user->getId()); ;
        /** @var UsersPreferences $userPreferences */
        $userPreferences = $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:UsersPreferences')->findOneBy(['iduser' => $user->getId()]);
        if (!$userPreferences) {
            $userPreferences = new UsersPreferences($user);
        } else if ($request->get("showSpecials")) {
            $userPreferences->togglePreference("ShowSpecials");
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($userPreferences);
        $em->flush();

        return new JsonResponse(['status' => 'success']);
    }

    public function listUsersAction(Request $request)
    {
        /** @var UsersRepository $usersRepo */
        $usersRepo = $this->getDoctrine()->getRepository('LoopAnime\UsersBundle\Entity\Users');

        $query = $usersRepo->getAllUsers();

        $paginator = $this->get('knp_paginator');
        /** @var Users $users */
        $users = $paginator->paginate(
            $query,
            $request->query->get('page', 1),
            $request->query->get('maxr', 10)
        );
        $stats = [];
        foreach ($users as $user) {
            $stats[$user->getId()] = $usersRepo->getStats($user);
        }
        return $this->render("LoopAnimeUsersBundle:users:listUsers.html.twig", array("users" => $users, "stats" => $stats));
    }

    public function getUserAction(Users $idUser, Request $request)
    {
        /** @var UsersFavoritesRepository $usersFavRepo */
        $usersFavRepo = $this->getDoctrine()->getRepository('LoopAnime\UsersBundle\Entity\UsersFavorites');
        /** @var CommentsRepository $commentsRepo */
        $commentsRepo = $this->getDoctrine()->getRepository('LoopAnime\CommentsBundle\Entity\Comments');

        $stats = $this->getDoctrine()->getRepository('LoopAnimeUsersBundle:Users')->getStats($idUser);
        /** @var UsersFavorites[] $favorites */
        $favorites = $usersFavRepo->getUsersFavoriteAnimes($idUser);
        $comments = $commentsRepo->getCommentsByUser($idUser);

        $animeStats = [];
        foreach ($favorites as $favorite) {
            $animeStats[$favorite['id']] = $favorite;
        }

        return $this->render("LoopAnimeUsersBundle:users:userProfile.html.twig", [
            "user" => $idUser,
            "favorites" => $favorites,
            "comments" => $comments,
            "stats" => $stats,
            "animeStats" => $animeStats
        ]);
    }

}
