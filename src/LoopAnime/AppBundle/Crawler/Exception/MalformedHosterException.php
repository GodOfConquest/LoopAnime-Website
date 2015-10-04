<?php

namespace LoopAnime\AppBundle\Crawler\Exception;

class MalformedHosterException extends \Exception
{

    public function __construct($message)
    {
        parent::__construct($message);
    }

}
