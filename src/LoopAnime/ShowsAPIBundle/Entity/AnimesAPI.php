<?php

namespace LoopAnime\ShowsAPIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LoopAnime\ShowsBundle\Entity\Animes;

/**
 * AnimesAPI
 *
 * @ORM\Table("animes_api")
 * @ORM\Entity(repositoryClass="LoopAnime\ShowsAPIBundle\Entity\AnimesAPIRepository")
 */
class AnimesAPI
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_anime_api", type="integer")
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
     * @var integer
     *
     * @ORM\Column(name="id_api", type="string")
     * @ORM\ManyToMany(targetEntity="LoopAnime\ShowsAPIBundle\Entity\APIS", cascade={"remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $idApi;

    /**
     * @var string
     *
     * @ORM\Column(name="api_anime_key", type="string", length=100)
     */
    private $apiAnimeKey;

    /**
     * @var Animes
     *
     * @ORM\ManyToOne(targetEntity="LoopAnime\ShowsBundle\Entity\Animes",inversedBy="animesApi")
     * @ORM\JoinColumn(name="id_anime", referencedColumnName="id_anime")
     */
    protected $anime;


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
     * @return AnimesAPI
     */
    public function setIdAnime($idAnime)
    {
        $this->idAnime = $idAnime;

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
     * Set idApi
     *
     * @param integer $idApi
     * @return AnimesApi
     */
    public function setIdApi($idApi)
    {
        $this->idApi = $idApi;

        return $this;
    }

    /**
     * Get idApi
     *
     * @return integer 
     */
    public function getIdApi()
    {
        return $this->idApi;
    }

    /**
     * Set apiAnimeKey
     *
     * @param string $apiAnimeKey
     * @return AnimesApi
     */
    public function setApiAnimeKey($apiAnimeKey)
    {
        $this->apiAnimeKey = $apiAnimeKey;

        return $this;
    }

    /**
     * Get apiAnimeKey
     *
     * @return string 
     */
    public function getApiAnimeKey()
    {
        return $this->apiAnimeKey;
    }

    public function getAnime()
    {
        return $this->anime;
    }

    public function setAnime(Animes $anime)
    {
        $this->anime = $anime;
    }
}
