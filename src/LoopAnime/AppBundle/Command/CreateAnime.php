<?php

namespace LoopAnime\AppBundle\Command;

use LoopAnime\AppBundle\Parser\ParserAnime;
use SimpleBus\Message\Type\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAnime implements Command {

    public $parserAnime;
    public $output;

    public function __construct(ParserAnime $parserAnime, OutputInterface $output)
    {
        $this->parserAnime = $parserAnime;
        $this->output = $output;
    }

}
