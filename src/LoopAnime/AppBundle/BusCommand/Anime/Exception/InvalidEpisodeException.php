<?php
namespace LoopAnime\AppBundle\BusCommand\Anime\Exception;


class InvalidEpisodeException extends \Exception
{

    public function __construct($message)
    {
        parent::__construct(sprintf('The anime is invalid. %s', $message));
    }

}
