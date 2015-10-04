<?php

namespace LoopAnime\UsersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LoopAnime\ShowsBundle\Entity\Animes;

/**
 * Users_Favorites
 *
 * @ORM\Table("users_favorites")
 * @ORM\Entity(repositoryClass="LoopAnime\UsersBundle\Entity\UsersFavoritesRepository")
 */
class UsersFavorites
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_favorite", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_anime", type="integer")
     */
    private $idAnime;

    /**
     * @var Animes
     *
     * @ORM\ManyToOne(targetEntity="LoopAnime\ShowsBundle\Entity\Animes",inversedBy="userFavorites")
     * @ORM\JoinColumn(name="id_anime", referencedColumnName="id_anime")
     */
    protected $anime;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_user", type="integer")
     * @ORM\ManyToOne(targetEntity="LoopAnime\UsersBundle\Entity\Users", cascade={"remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $idUser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="datetime")
     */
    private $createTime;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set idAnime
     *
     * @param integer $idAnime
     * @return UsersFavorites
     */
    public function setIdAnime($idAnime)
    {
        $this->idAnime = $idAnime;
        return $this;
    }

    /**
     * @param Animes $anime
     * @return $this
     */
    public function setAnime(Animes $anime)
    {
        $this->anime = $anime;
        return $this;
    }

    /**
     * Get idAnime
     *
     * @return integer 
     */
    public function getIdAnime()
    {
        return $this->idAnime;
    }

    /**
     * @return Animes
     */
    public function getAnime()
    {
        return $this->anime;
    }

    /**
     * Set idUser
     *
     * @param integer $idUser
     * @return UsersFavorites
     */
    public function setIdUser($idUser)
    {
        $this->idUser = $idUser;

        return $this;
    }

    /**
     * Get idUser
     *
     * @return integer 
     */
    public function getIdUser()
    {
        return $this->idUser;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     * @return UsersFavorites
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * Get createTime
     *
     * @return \DateTime 
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }
}
