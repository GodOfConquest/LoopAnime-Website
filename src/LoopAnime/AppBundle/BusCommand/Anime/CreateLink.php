<?php
namespace LoopAnime\AppBundle\BusCommand\Anime;

use LoopAnime\AppBundle\Crawler\Hoster\HosterInterface;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use SimpleBus\Message\Type\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CreateLink implements Command
{

    protected $episode;
    protected $hoster;
    protected $mirrors;
    protected $output;

    public function __construct(AnimesEpisodes $episode, HosterInterface $hoster, array $mirrors, OutputInterface $output)
    {
        $this->episode = $episode;
        $this->hoster = $hoster;
        $this->mirrors = $mirrors;
        $this->output = $output;
    }

    /**
     * @return AnimesEpisodes
     */
    public function getEpisode()
    {
        return $this->episode;
    }

    /**
     * @return HosterInterface
     */
    public function getHoster()
    {
        return $this->hoster;
    }

    /**
     * @return array
     */
    public function getMirrors()
    {
        return $this->mirrors;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }


}
