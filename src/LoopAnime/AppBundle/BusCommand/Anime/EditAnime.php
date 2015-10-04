<?php

namespace LoopAnime\AppBundle\BusCommand\Anime;

use LoopAnime\AppBundle\Parser\ParserAnime;
use LoopAnime\ShowsBundle\Entity\Animes;
use SimpleBus\Message\Type\Command;
use Symfony\Component\Console\Output\OutputInterface;

class EditAnime implements Command {

    /** @var Animes */
    public $anime;
    /** @var ParserAnime */
    public $parserAnime;
    /** @var OutputInterface */
    public $output;

    public function __construct($anime, $parserAnime, OutputInterface $output = null)
    {
        $this->anime = $anime;
        $this->parserAnime = $parserAnime;
        $this->output = $output;
    }

}
