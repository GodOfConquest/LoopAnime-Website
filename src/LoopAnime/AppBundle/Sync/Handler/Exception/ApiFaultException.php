<?php

namespace LoopAnime\AppBundle\Sync\Handler\Exception;


class ApiFaultException extends \Exception {

    public function __construct($api, $error)
    {
        parent::__construct(sprintf('API %s has returned an error: %s', $api, $error));
    }

}
