<?php

namespace LoopAnime\ShowsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Animes_Track
 *
 * @ORM\Table("animes_track")
 * @ORM\Entity(repositoryClass="LoopAnime\Bundle\ShowsBundle\Entity\Animes_TrackRepository")
 */
class Animes_Track
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_track", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_anime", type="integer")
     * @ORM\ManyToOne(targetEntity="LoopAnime\ShowsBundle\Entity\Animes", cascade={"remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $idAnime;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_user", type="integer")
     * @ORM\ManyToOne(targetEntity="LoopAnime\UsersBundle\Entity\Users", cascade={"remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $idUser;

    /**
     * @var integer
     *
     * @ORM\Column(name="last_episode_id", type="integer")
     */
    private $lastEpisodeId;


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
     * @return Animes_Track
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
     * Set idUser
     *
     * @param integer $idUser
     * @return Animes_Track
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
     * Set lastEpisodeId
     *
     * @param integer $lastEpisodeId
     * @return Animes_Track
     */
    public function setLastEpisodeId($lastEpisodeId)
    {
        $this->lastEpisodeId = $lastEpisodeId;

        return $this;
    }

    /**
     * Get lastEpisodeId
     *
     * @return integer 
     */
    public function getLastEpisodeId()
    {
        return $this->lastEpisodeId;
    }
}
