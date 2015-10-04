<?php

namespace LoopAnime\ShowsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * animes_episodes
 *
 * @ORM\Table("animes_episodes")
 * @ORM\Entity(repositoryClass="LoopAnime\ShowsBundle\Entity\AnimesEpisodesRepository")
 */
class AnimesEpisodes
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_episode", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="LoopAnime\ShowsBundle\Entity\AnimesSeasons", inversedBy="animesEpisodes")
     * @ORM\JoinColumn(name="id_season", referencedColumnName="id_season")
     */
    private $season;

    /**
     * @var integer
     *
     * @ORM\Column(name="episode", type="integer")
     */
    private $episode;

    /**
     * @var string
     *
     * @ORM\Column(name="episode_title", type="string", length=250)
     */
    private $episodeTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="poster", type="string", length=255)
     */
    private $poster;

    /**
     * @var integer
     *
     * @ORM\Column(name="rating", type="integer")
     */
    private $rating;

    /**
     * @var integer
     *
     * @ORM\Column(name="views", type="integer")
     */
    private $views;

    /**
     * @var integer
     *
     * @ORM\Column(name="comments", type="integer")
     */
    private $comments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="air_date", type="datetime")
     */
    private $airDate;

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="text")
     */
    private $summary;

    /**
     * @var integer
     *
     * @ORM\Column(name="ratingCount", type="integer")
     */
    private $ratingCount;

    /**
     * @var string
     *
     * @ORM\Column(name="imdb_id", type="string", length=10)
     */
    private $imdbId;

    /**
     * @var integer
     *
     * @ORM\Column(name="ratingUp", type="integer")
     */
    private $ratingUp;

    /**
     * @var integer
     *
     * @ORM\Column(name="ratingDown", type="integer")
     */
    private $ratingDown;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_updated", type="datetime")
     */
    private $lastUpdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="datetime")
     */
    private $createTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="absolute_number", type="integer")
     */
    private $absoluteNumber;

    /**
     * @ORM\OneToMany(targetEntity="LoopAnime\ShowsBundle\Entity\Views", mappedBy="animeEpisodes")
     * @ORM\JoinColumn(name="id_episode", referencedColumnName="id_episode", nullable=true)
     */
    protected $episodeViews;

    /**
     * @ORM\OneToMany(targetEntity="LoopAnime\ShowsBundle\Entity\AnimesLinks", mappedBy="episode")
     * @ORM\JoinColumn(name="id_episode", referencedColumnName="id_episode", nullable=true)
     */
    protected $links;

    public function __construct()
    {
        $this->rating = 0;
        $this->ratingCount = 0;
        $this->ratingDown = 0;
        $this->ratingUp = 0;
        $this->comments = 0;
        $this->links = null;
        $this->episodeViews = null;
        $this->imdbId = 0;
        $this->summary = "";
        $this->airDate = new \DateTime('now');
        $this->views = 0;
        $this->episodeTitle = 'TBA';
        $this->poster = 'TBA';

        $this->absoluteNumber = 0;
        $this->lastUpdate = new \DateTime("now");
        $this->createTime = new \DateTime("now");
    }

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
     * Set idSeason
     *
     * @param AnimesSeasons $season
     * @return AnimesEpisodes
     */
    public function setSeason($season)
    {
        $this->season = $season;

        return $this;
    }

    /**
     * Get idSeason
     *
     * @return AnimesSeasons
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * Set episode
     *
     * @param integer $episode
     * @return AnimesEpisodes
     */
    public function setEpisode($episode)
    {
        $this->episode = $episode;

        return $this;
    }

    /**
     * Get episode
     *
     * @return integer 
     */
    public function getEpisode()
    {
        return $this->episode;
    }

    /**
     * Set episodeTitle
     *
     * @param string $episodeTitle
     * @return AnimesEpisodes
     */
    public function setEpisodeTitle($episodeTitle)
    {
        $this->episodeTitle = $episodeTitle;

        return $this;
    }

    /**
     * Get episodeTitle
     *
     * @return string 
     */
    public function getEpisodeTitle()
    {
        return $this->episodeTitle;
    }

    /**
     * Set poster
     *
     * @param string $poster
     * @return AnimesEpisodes
     */
    public function setPoster($poster)
    {
        $this->poster = $poster;

        return $this;
    }

    /**
     * Get poster
     *
     * @return string 
     */
    public function getPoster()
    {
        if (!empty($this->poster)) {
            return $this->poster;
        }
        return $this->getSeason()->getAnime()->getPoster();
    }

    /**
     * Set rating
     *
     * @param integer $rating
     * @return AnimesEpisodes
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return integer 
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set views
     *
     * @param integer $views
     * @return AnimesEpisodes
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get views
     *
     * @return integer 
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Set comments
     *
     * @param integer $comments
     * @return AnimesEpisodes
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return integer 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set airDate
     *
     * @param \DateTime $airDate
     * @return AnimesEpisodes
     */
    public function setAirDate(\DateTime $airDate)
    {
        $this->airDate = $airDate;

        return $this;
    }

    /**
     * Get airDate
     *
     * @return \DateTime 
     */
    public function getAirDate()
    {
        return $this->airDate;
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return AnimesEpisodes
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set ratingCount
     *
     * @param integer $ratingCount
     * @return AnimesEpisodes
     */
    public function setRatingCount($ratingCount)
    {
        $this->ratingCount = $ratingCount;

        return $this;
    }

    /**
     * Get ratingCount
     *
     * @return integer 
     */
    public function getRatingCount()
    {
        return $this->ratingCount;
    }

    /**
     * Set imdbId
     *
     * @param string $imdbId
     * @return AnimesEpisodes
     */
    public function setImdbId($imdbId)
    {
        $this->imdbId = $imdbId;

        return $this;
    }

    /**
     * Get imdbId
     *
     * @return string 
     */
    public function getImdbId()
    {
        return $this->imdbId;
    }

    /**
     * Set ratingUp
     *
     * @param integer $ratingUp
     * @return AnimesEpisodes
     */
    public function setRatingUp($ratingUp)
    {
        $this->ratingUp = $ratingUp;

        return $this;
    }

    /**
     * Get ratingUp
     *
     * @return integer 
     */
    public function getRatingUp()
    {
        return $this->ratingUp;
    }

    /**
     * Set ratingDown
     *
     * @param integer $ratingDown
     * @return AnimesEpisodes
     */
    public function setRatingDown($ratingDown)
    {
        $this->ratingDown = $ratingDown;

        return $this;
    }

    /**
     * Get ratingDown
     *
     * @return integer 
     */
    public function getRatingDown()
    {
        return $this->ratingDown;
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     * @return AnimesEpisodes
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime 
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     * @return AnimesEpisodes
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

    /**
     * Set absoluteNumber
     *
     * @param integer $absoluteNumber
     * @return AnimesEpisodes
     */
    public function setAbsoluteNumber($absoluteNumber)
    {
        $this->absoluteNumber = $absoluteNumber;

        return $this;
    }

    /**
     * Get absoluteNumber
     *
     * @return integer 
     */
    public function getAbsoluteNumber()
    {
        if (empty($this->absoluteNumber)) {
            $this->absoluteNumber = $this->calculateAbsoluteNumber();
        }
        return $this->absoluteNumber;
    }

    /**
     * @return array
     */
    public function convert2Array()
    {
        return array(
            "id" => $this->getId(),
            "poster" => $this->getPoster(),
            "idSeason" => $this->getSeason()->getId(),
            "airDate" => $this->getAirDate(),
            "absoluteNumber" => $this->getAbsoluteNumber(),
            "views" => $this->getViews(),
            "title" => $this->getEpisodeTitle(),
            "episodeNumber" => $this->getEpisode(),
            "rating" => $this->getRating(),
            "summary" => $this->getSummary(),
            "ratingUp" => $this->getRatingUp(),
            "ratingDown" => $this->getRatingDown()
        );
    }

    public function __toString()
    {
        return (string)$this->getEpisode();
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function calculateAbsoluteNumber()
    {
        $seasons = $this->getSeason()->getAnime()->getAnimeSeasons();
        $absolute = 0;
        foreach ($seasons as $season) {
            // Ignore Special Season
            if ($season->getSeason() == 0) {
                continue;
            }
            // If the season is the same as the user grab the absolute till now + episode number (eg: 100 + 1)
            if ($season->getSeason() === $this->getSeason()->getSeason()) {
                return $absolute + $this->getEpisode();
            }
            // If not found -- add all episodes of the season to the absolute counter
            $absolute += $season->getNumberEpisodes();
        }
        return $absolute;
    }

    public function hasLinks()
    {
        $links = $this->links->first();
        if ($links) {
            return true;
        }
        return false;
    }

}
