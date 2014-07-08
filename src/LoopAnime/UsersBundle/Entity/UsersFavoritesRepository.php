<?php

namespace LoopAnime\UsersBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Users_FavoritesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UsersFavoritesRepository extends EntityRepository
{
    public function getAnimeFavorite($idAnime, $idUser)
    {
        $query = "SELECT id_anime FROM users_favorites WHERE id_anime = '$idAnime' AND id_user = '$idUser'";

        return $this->_em->createQuery($query)->getOneOrNullResult();

    }

    public function getUsersFavoriteAnimes(Users $user, $getQuery = true)
    {

        $query = $this->createQueryBuilder("users_favorites")
            ->select('users_favorites')
            ->addselect("animes")
            ->addSelect('(SELECT COUNT(animesSeasons2.id) FROM LoopAnime\ShowsBundle\Entity\AnimesSeasons animesSeasons2 WHERE animesSeasons2.idAnime = animes.id) AS total_seasons')
            ->addSelect('users_favorites.id')
            ->addSelect('SUM(animes_seasons.numberEpisodes)')
            ->addSelect('(SELECT COUNT(views.id) FROM LoopAnime\ShowsBundle\Entity\Views views
                            JOIN views.animeEpisodes animes_episodes2
                            JOIN animes_episodes2.animesSeasons animes_seasons3
                            JOIN animes_seasons3.animes animes3
                            WHERE animes3.id = animes.id) AS total_saw')
            ->join('users_favorites.anime','animes')
            ->join('animes.animesSeasons','animes_seasons')
            ->join('animes_seasons.animesEpisodes','animes_episodes')
            ->where('users_favorites.idUser = :idUser')
            ->setParameter('idUser',$user->getId())
            ->groupBy('animes.id');

//        $where_clause = "users_favorites.id_user = '".$user->getId()."'";
//
//        $query = "SELECT
//					users_favorites.id_favorite,
//					animes.id AS `id_anime`,
//					animes.title,
//					animes.last_updated,
//					animes.status,
//					animes.poster,
//					(SELECT COUNT(*) FROM LoopAnime\ShowsBundle\Entity\AnimesSeasons animes_seasons WHERE animes_seasons.id_anime = animes.id) AS `total_seasons`,
//					SUM(animes_seasons.number_episodes) AS `total_episodes`,
//					(SELECT COUNT(*) FROM LoopAnime\ShowsBundle\Entity\Views views
//						JOIN LoopAnime\ShowsBundle\Entity\AnimesEpisodes animes_episodes
//						JOIN LoopAnime\ShowsBundle\Entity\AnimesSeasons animes_seasons
//						JOIN LoopAnime\ShowsBundle\Entity\Animes animes
//					WHERE animes.id = @id_anime AND views.id_user = users_favorites.id_user) AS `total_saw`
//				  FROM
//						LoopAnime\UsersBundle\Entity\UsersFavorites users_favorites
//						JOIN LoopAnime\ShowsBundle\Entity\Animes animes
//						JOIN LoopAnime\ShowsBundle\Entity\AnimesSeasons animes_seasons
//				  WHERE
//						$where_clause
//				  GROUP BY animes.id_anime";

        if($getQuery) {
            return $query->getQuery();
        } else {
            return $query->getQuery()->getResult();
        }
    }
}
