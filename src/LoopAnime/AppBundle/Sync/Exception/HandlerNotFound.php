<?php
namespace LoopAnime\AppBundle\Sync\Exception;

class HandlerNotFound extends \Exception
{

    protected $message = 'Handler %s was not found or injected!';

    public function __construct($handler)
    {
        parent::__construct(sprintf($this->message, $handler));
    }
}
