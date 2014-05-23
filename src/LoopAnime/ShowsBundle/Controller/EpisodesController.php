<?php

namespace LoopAnime\ShowsBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use LoopAnime\Helpers\Crawlers\Crawler;
use LoopAnime\HelpersCrawlers\Anime44;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesLinks;
use LoopAnime\UsersBundle\Entity\Users;
use LoopAnime\UsersBundle\Entity\UsersPreferences;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EpisodesController extends Controller
{
    public function indexAction(Request $request)
    {
        return self::listEpisodesAction("html", $request);
    }

    public function listEpisodesAction($_format, Request $request)
    {
        $episodesRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes');

        if(!$request->get("anime") && !$request->get("season")) {
            throw new \Exception("Please look for an sepecific anime id or season id to retrieve episodes.");
        }

        /** @var AnimesEpisodes[] $episodes */
        $episodes = null;
        if($request->get("anime")) {
            $manager = $this->getDoctrine()->getManager();
            $episodes = $manager->createQuery("
                SELECT ae.id, ae.idSeason, ae.poster, ae.airDate, ae.absoluteNumber, ae.views, ae.episodeTitle, ae.episode, ae.rating, ae.summary, ae.ratingUp, ae.ratingDown
                FROM
                    LoopAnime\ShowsBundle\Entity\Animes a
                    JOIN a.animesSeasons ase
                    JOIN ase.animesEpisodes ae
                WHERE
                    a.id = '".$request->get("anime")."'
                ")->getResult();

        } elseif($request->get("season")) {
            $episodes = $episodesRepo->findBy(array("idSeason" => $request->get("season")));
        }

        if(empty($episodes)) {
            throw new \Exception("Error retrieving the episodes from the anime / season selected");
        }

        // TODO maybe i can serialize the doctrine? and avoid those dumb tests
        if($episodes[0] instanceof AnimesEpisodes) {
            foreach($episodes as $episodeInfo) {
                $episode = [];
                $episode["id"] = $episodeInfo->getId();
                $episode["poster"] = $episodeInfo->getPoster();
                $episode["idSeason"] = $episodeInfo->getIdSeason();
                $episode["airDate"] = $episodeInfo->getAirDate();
                $episode["absoluteNumber"] = $episodeInfo->getAbsoluteNumber();
                $episode["views"] = $episodeInfo->getViews();
                $episode["title"] = $episodeInfo->getEpisodeTitle();
                $episode["episodeNumber"] = $episodeInfo->getEpisode();
                $episode["rating"] = $episodeInfo->getRating();
                $episode["summary"] = $episodeInfo->getSummary();
                $episode["ratingUp"] = $episodeInfo->getRatingUp();
                $episode["ratingDown"] = $episodeInfo->getRatingDown();

                $data["payload"]["episodes"][] = $episode;
            }
        } else {
            $data["payload"]["episodes"] = $episodes;
        }
        
        if($_format === "html") {
            $render = $this->render("LoopAnimeShowsBundle:Default:animeInfo.html.twig", array("animes" => $data["payload"]["animes"]));
            return $render;
        } elseif($_format === "json") {
            return new JsonResponse($data);
        }

    }


    public function getEpisodeAction($idEpisode, $_format, Request $request)
    {

        $episodesRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes');

        /** @var AnimesEpisodes $episodes */
        $episodes = $episodesRepo->find($idEpisode);

        if(empty($episodes)) {
            throw new \Exception("The anime does not exists or was removed.");
        }

        $episodeInfo = &$episodes;
        $episode = [];
        $episode["id"] = $episodeInfo->getId();
        $episode["poster"] = $episodeInfo->getPoster();
        $episode["airDate"] = $episodeInfo->getAirDate();
        $episode["absoluteNumber"] = $episodeInfo->getAbsoluteNumber();
        $episode["views"] = $episodeInfo->getViews();
        $episode["title"] = $episodeInfo->getEpisodeTitle();
        $episode["episodeNumber"] = $episodeInfo->getEpisode();
        $episode["rating"] = $episodeInfo->getRating();
        $episode["summary"] = $episodeInfo->getSummary();
        $episode["ratingUp"] = $episodeInfo->getRatingUp();
        $episode["ratingDown"] = $episodeInfo->getRatingDown();

        $data["payload"]["episodes"][] = $episode;

        if($_format === "html") {
            $render = $this->render("LoopAnimeShowsBundle:Default:animeInfo.html.twig", array("animes" => $data["payload"]["animes"]));
            return $render;
        } elseif($_format === "json") {
            return new JsonResponse($data);
        }

    }


    public function getEpisodesAction($_format, Request $request)
    {

        $entityManager = $this->getDoctrine()->getManager();

        /** @var Users $user */
        $user = $this->getUser();
        if($user) {
            $userPreferences = new UsersPreferences();
            $userPreferences->setIdUser($user);
        }

        $maxResults = 20;
        if($request->get("maxr")) {
            $maxResults = $request->get("maxr");
        }

        $skip = 0;
        if($request->get("skip")) {
            $skip = $request->get("skip");
        }

        $typeEpisode = "recent";
        if($request->get("typeEpisode")) {
            $typeEpisode = $request->get("typeEpisode");
        }

        $where = "ae.airDate <= CURRENT_TIMESTAMP()";

        switch($typeEpisode) {
            case "recent":
                $orderBy = "ae.airDate DESC";
                $dql = 'SELECT ae FROM LoopAnime\ShowsBundle\Entity\AnimesEpisodes ae WHERE '.$where.' ORDER BY ' . $orderBy;
                break;
            case "mostview":
                $orderBy = "ae.views DESC";
                $dql = 'SELECT ae FROM LoopAnime\ShowsBundle\Entity\AnimesEpisodes ae WHERE '.$where.' ORDER BY ' . $orderBy;
                break;
            case "mostrated":
                $orderBy = "ae.rating DESC, ae.ratingCount DESC, ae.ratingUp DESC";
                $dql = 'SELECT ae FROM LoopAnime\ShowsBundle\Entity\AnimesEpisodes ae WHERE '.$where.' ORDER BY ' . $orderBy;
                break;
            case "userRecent":
                if(!$user) {
                    throw new \Exception("You need to be logged to see this content");
                }

                $order = "DESC";
                if($userPreferences->getTrackEpisodesSort())
                    $order = $userPreferences->getTrackEpisodesSort();

                $orderBy = "animes_seasons.season $order, animes_episodes.episode $order";
                $dql = '
                SELECT animesEpisodes FROM
                    LoopAnime\UsersBundle\Entity\UsersFavorites uf
						JOIN uf.anime animes
						JOIN animes.animesSeasons animesSeasons
                        JOIN animesSeasons.AnimesEpisodes animesEpisodes
                    WHERE '.$where.' ORDER BY ' . $orderBy;
                break;
            case "userFuture":
                if(!$user) {
                    throw new \Exception("You need to be logged to see this content");
                }

                // User Preferences
                if($userPreferences->getFutureListSpecials())
                    $where .= " AND animesSeasons.season > 0";

                $orderBy = "ae.airDate ASC";
                $dql = '
                SELECT animesEpisodes FROM
                    LoopAnime\UsersBundle\Entity\UsersFavorites uf
						JOIN uf.idAnime animes
						JOIN animes.AnimesSeasons animesSeasons
                        JOIN animesSeasons.AnimesEpisodes animesEpisodes
                    WHERE '.$where.' ORDER BY ' . $orderBy;
                break;
                break;
            case "userHistory":
                if(!$user) {
                    throw new \Exception("You need to be logged to see this content");
                }
                break;
        }


        $query = $entityManager->createQuery($dql)
            ->setFirstResult($skip)
            ->setMaxResults($maxResults);

        /** @var AnimesEpisodes[] $recentEpisodes */
        $recentEpisodes = new Paginator($query, $fetchJoinCollection = true);

        if(!$recentEpisodes) {
            return new JsonResponse(array("failure"=>true,"msg"=>"There isn't any recent episodes today!"));
        }

        $data =[];

        foreach ($recentEpisodes as $episode) {
            $data["payload"]["animes"]["episodes"][] = array(
                "id" => $episode->getId(),
                "url" => $episode->getEpisode(),
                "poster" => $episode->getPoster(),
                "title" => $episode->getEpisodeTitle(),
                "views" => $episode->getViews(),
                "rating" => $episode->getRating(),
            );

        }

        if($_format === "html") {
            $render = $this->render("LoopAnimeShowsBundle:Default:videoGallery.html.twig", array("recentsEpisodes" => $data["payload"]["animes"]["episodes"]));
            return $render;
        } elseif($_format === "json") {
            return new JsonResponse($data);
        }
    }

    public function getLinksAction($_format, Request $request)
    {
        $linksRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesLinks');

        /** @var AnimesLinks[] $links */
        $links = null;
        if($request->get("episode")) {
            $links = $linksRepo->findBy(array("idEpisode" => $request->get("episode")));
        } else {
            throw new \Exception("Please provide a get value with the id of the Episode");
        }

        if(empty($links)) {
            throw new \Exception("Error retrieving the episodes links");
        }

        foreach($links as $linkInfo) {
            $link = [];
            $link["id"] = $linkInfo->getId();
            $link["lang"] = $linkInfo->getLang();
            $link["createTime"] = $linkInfo->getCreateTime();
            $link["fileServer"] = $linkInfo->getFileServer();
            $link["fileSize"] = $linkInfo->getFileSize();
            $link["hoster"] = $linkInfo->getHoster();
            $link["subtitles"] = $linkInfo->getSubtitles();
            $link["subtitlesLang"] = $linkInfo->getSubLang();
            $link["qualityType"] = $linkInfo->getQualityType();
            $link["fileType"] = $linkInfo->getFileType();
            $link["link"] = $linkInfo->getLink();
            $link["used"] = $linkInfo->getUsed();
            $link["usedTimes"] = $linkInfo->getUsedTimes();
            $link["status"] = $linkInfo->getStatus();

            $data["payload"]["episodes"][] = $link;
        }

        if($_format === "html") {
            $render = $this->render("LoopAnimeShowsBundle:Default:animeInfo.html.twig", array("animes" => $data["payload"]["animes"]));
            return $render;
        } elseif($_format === "json") {
            return new JsonResponse($data);
        }

    }

    public function getDirectLinkAction($idLink, $_format, Request $request)
    {

        $linksRepo = $this->getDoctrine()->getRepository('LoopAnime\ShowsBundle\Entity\AnimesLinks');

        /** @var AnimesLinks $links */
        $links = $linksRepo->find($idLink);

        if(empty($links)) {
            throw new \Exception("Error retrieving the episodes links");
        }

        $linkInfo = &$links;
        $link = [];
        $link["id"] = $linkInfo->getId();
        $link["lang"] = $linkInfo->getLang();
        $link["createTime"] = $linkInfo->getCreateTime();
        $link["fileServer"] = $linkInfo->getFileServer();
        $link["fileSize"] = $linkInfo->getFileSize();
        $link["hoster"] = $linkInfo->getHoster();
        $link["subtitles"] = $linkInfo->getSubtitles();
        $link["subtitlesLang"] = $linkInfo->getSubLang();
        $link["qualityType"] = $linkInfo->getQualityType();
        $link["fileType"] = $linkInfo->getFileType();
        $link["link"] = $linkInfo->getLink();
        $link["videoOptions"] = Crawler::crawlVideoLink(explode("-",$linkInfo->getHoster())[0],$linkInfo->getLink());
        $link["used"] = $linkInfo->getUsed();
        $link["usedTimes"] = $linkInfo->getUsedTimes();
        $link["status"] = $linkInfo->getStatus();

        $data["payload"]["episodes"][] = $link;

        if($_format === "html") {
            $render = $this->render("LoopAnimeShowsBundle:Default:animeInfo.html.twig", array("animes" => $data["payload"]["animes"]));
            return $render;
        } elseif($_format === "json") {
            return new JsonResponse($data);
        }

    }

}
