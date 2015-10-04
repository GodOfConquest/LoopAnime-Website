<?php
namespace LoopAnime\AppBundle\Crawler\Exception;


class InvalidHosterException extends \InvalidArgumentException
{

    public function __construct($hoster)
    {
        parent::__construct(sprintf("Hoster %s is not intialized or dont exist", $hoster));
    }

}
