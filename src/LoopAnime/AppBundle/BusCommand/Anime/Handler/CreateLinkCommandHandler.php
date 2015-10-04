<?php

namespace LoopAnime\AppBundle\BusCommand\Anime\Handler;

use Doctrine\ORM\EntityManager;
use LoopAnime\AppBundle\BusCommand\Anime\CreateLink;
use LoopAnime\AppBundle\Crawler\Hoster\HosterInterface;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesLinks;
use SimpleBus\Message\Handler\MessageHandler;
use SimpleBus\Message\Message;
use Symfony\Component\Console\Output\OutputInterface;

class CreateLinkCommandHandler implements MessageHandler {

    private $em;
    /** @var OutputInterface */
    private $output;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Handles the given message.
     *
     * @param Message|CreateLink $message
     * @return void
     */
    public function handle(Message $message)
    {
        $this->output = $message->getOutput();
        foreach ($message->getMirrors() as $mirror) {
            if ($this->validateMirror($mirror))
                $this->createLink($message->getEpisode(), $message->getHoster(), $mirror);
        }
    }

    private function validateMirror($mirror)
    {
        if (empty($mirror)) {
            return false;
        }
        return true;
    }

    private function createLink(AnimesEpisodes $episode, HosterInterface $hoster, $mirror)
    {
        $url = parse_url($mirror);

        $link = New AnimesLinks();
        $link->setEpisode($episode);
        $link->setHoster($hoster->getName());
        $link->setLink($mirror);
        $link->setStatus(1);
        $link->setIdUser(0);
        $sublang = $hoster->getSubtitles();
        $link->setLang("JAP");
        $link->setSubtitles((!empty($sublang) ? 1 : 0));
        $link->setSubLang($sublang);
        $link->setFileType("mp4");
        $link->setCreateTime(new \DateTime("now"));
        $link->setUsed(0);
        $link->setUsedTimes(0);
        $link->setReport(0);
        $link->setQualityType('SQ');
        $link->setFileServer($url['host']);
        $link->setFileSize("0");

        $this->em->persist($link);
        $this->em->flush();
        $this->output->writeln('Link ' . $mirror . ' has been inserted successfully for the episode ' . $episode->getEpisode() . '!');
    }

}
