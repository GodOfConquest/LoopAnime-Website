<?php

namespace LoopAnime\ShowsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Animes_Links
 *
 * @ORM\Table("animes_links")
 * @ORM\Entity(repositoryClass="LoopAnime\ShowsBundle\Entity\AnimesLinksRepository")
 */
class AnimesLinks
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_link", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_episode", type="integer")
     */
    private $idEpisode;

    /**
     * @var string
     *
     * @ORM\Column(name="hoster", type="string", length=255)
     */
    private $hoster;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255)
     */
    private $link;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_user", type="integer")
     */
    private $idUser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="datetime")
     */
    private $createTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="subtitles", type="integer")
     */
    private $subtitles;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=5)
     */
    private $lang;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_lang", type="string", length=5)
     */
    private $subLang;

    /**
     * @var integer
     *
     * @ORM\Column(name="used", type="integer")
     */
    private $used;

    /**
     * @var string
     *
     * @ORM\Column(name="file_type", type="string", length=10)
     */
    private $fileType;

    /**
     * @var integer
     *
     * @ORM\Column(name="report", type="integer")
     */
    private $report;

    /**
     * @var string
     *
     * @ORM\Column(name="quality_type", type="string", length=10)
     */
    private $qualityType;

    /**
     * @var string
     *
     * @ORM\Column(name="file_size", type="string", length=10)
     */
    private $fileSize;

    /**
     * @var string
     *
     * @ORM\Column(name="file_server", type="string", length=50)
     */
    private $fileServer;

    /**
     * @var integer
     *
     * @ORM\Column(name="used_times", type="integer")
     */
    private $usedTimes;

    /**
     * @ORM\ManyToOne(targetEntity="LoopAnime\ShowsBundle\Entity\AnimesEpisodes", inversedBy="links")
     * @ORM\JoinColumn(name="id_episode", referencedColumnName="id_episode")
     */
    protected $episode;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function setEpisode(AnimesEpisodes $episode)
    {
        $this->episode = $episode;
        $this->idEpisode = $episode;

        return $this;
    }

    /**
     * Get idEpisode
     *
     * @return integer 
     */
    public function getIdEpisode()
    {
        return $this->idEpisode;
    }

    /**
     * Set hoster
     *
     * @param string $hoster
     * @return AnimesLinks
     */
    public function setHoster($hoster)
    {
        $this->hoster = $hoster;

        return $this;
    }

    /**
     * Get hoster
     *
     * @return string 
     */
    public function getHoster()
    {
        return $this->hoster;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return AnimesLinks
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string 
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return AnimesLinks
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set idUser
     *
     * @param integer $idUser
     * @return AnimesLinks
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
     * @return AnimesLinks
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
     * Set subtitles
     *
     * @param integer $subtitles
     * @return AnimesLinks
     */
    public function setSubtitles($subtitles)
    {
        $this->subtitles = $subtitles;

        return $this;
    }

    /**
     * Get subtitles
     *
     * @return integer 
     */
    public function getSubtitles()
    {
        return $this->subtitles;
    }

    /**
     * Set lang
     *
     * @param string $lang
     * @return AnimesLinks
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string 
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set subLang
     *
     * @param string $subLang
     * @return AnimesLinks
     */
    public function setSubLang($subLang)
    {
        $this->subLang = $subLang;

        return $this;
    }

    /**
     * Get subLang
     *
     * @return string 
     */
    public function getSubLang()
    {
        return $this->subLang;
    }

    /**
     * Set used
     *
     * @param integer $used
     * @return AnimesLinks
     */
    public function setUsed($used)
    {
        $this->used = $used;

        return $this;
    }

    /**
     * Get used
     *
     * @return integer 
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Set fileType
     *
     * @param string $fileType
     * @return AnimesLinks
     */
    public function setFileType($fileType)
    {
        $this->fileType = $fileType;

        return $this;
    }

    /**
     * Get fileType
     *
     * @return string 
     */
    public function getFileType()
    {
        return $this->fileType;
    }

    /**
     * Set report
     *
     * @param integer $report
     * @return AnimesLinks
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return integer 
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Set qualityType
     *
     * @param string $qualityType
     * @return AnimesLinks
     */
    public function setQualityType($qualityType)
    {
        $this->qualityType = $qualityType;

        return $this;
    }

    /**
     * Get qualityType
     *
     * @return string 
     */
    public function getQualityType()
    {
        return $this->qualityType;
    }

    /**
     * Set fileSize
     *
     * @param string $fileSize
     * @return AnimesLinks
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get fileSize
     *
     * @return string 
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Set fileServer
     *
     * @param string $fileServer
     * @return AnimesLinks
     */
    public function setFileServer($fileServer)
    {
        $this->fileServer = $fileServer;

        return $this;
    }

    /**
     * Get fileServer
     *
     * @return string 
     */
    public function getFileServer()
    {
        return $this->fileServer;
    }

    /**
     * Set usedTimes
     *
     * @param integer $usedTimes
     * @return AnimesLinks
     */
    public function setUsedTimes($usedTimes)
    {
        $this->usedTimes = $usedTimes;

        return $this;
    }

    /**
     * Get usedTimes
     *
     * @return integer 
     */
    public function getUsedTimes()
    {
        return $this->usedTimes;
    }

    public function convert2Array()
    {
        return array(
            "id"            => $this->getId(),
            "lang"          => $this->getLang(),
            "createTime"    => $this->getCreateTime(),
            "fileServer"    => $this->getFileServer(),
            "fileSize"      => $this->getFileSize(),
            "hoster"        => $this->getHoster(),
            "subtitles"     => $this->getSubtitles(),
            "subtitlesLang" => $this->getSubLang(),
            "qualityType"   => $this->getQualityType(),
            "fileType"      => $this->getFileType(),
            "link"          => $this->getLink(),
            "used"          => $this->getUsed(),
            "usedTimes"     => $this->getUsedTimes(),
            "status"        => $this->getStatus(),
        );
    }
}
