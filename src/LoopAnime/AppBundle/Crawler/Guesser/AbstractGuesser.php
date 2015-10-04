<?php
namespace LoopAnime\AppBundle\Crawler\Guesser;


use LoopAnime\AppBundle\Crawler\Enum\GuessTypesEnum;

abstract class AbstractGuesser implements GuesserInterface
{

    protected $bestMatch;

    public function isExactMatch()
    {
        return $this->bestMatch['type'] === GuessTypesEnum::EXACT_MATCH;
    }

    public function getLog()
    {
        if ($this->bestMatch['type'] === GuessTypesEnum::NO_MATCH) {
            return 'NO MATCH';
        }
        return $this->bestMatch['match1'] . " === " . $this->bestMatch['match2'] . " (" . $this->bestMatch['percentage'] . ")";
    }

    public function getUri()
    {
        if ($this->bestMatch['type'] === GuessTypesEnum::NO_MATCH) {
            return false;
        }
        return $this->bestMatch['uri'];
    }

    public function getCompPercentage()
    {
        return !empty($this->bestMatch['percentage']) ? $this->bestMatch['percentage'] : 0;
    }

}
