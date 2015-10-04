<?php
namespace LoopAnime\AppBundle\Crawler\Exception;


class InvalidStrategyException extends \InvalidArgumentException
{

    public function __construct($strategy)
    {
        parent::__construct(sprintf("Strategy %s is not intialized or dont exist", $strategy));
    }

}
