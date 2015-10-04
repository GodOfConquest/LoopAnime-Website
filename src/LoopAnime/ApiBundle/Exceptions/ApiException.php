<?php
namespace LoopAnime\ApiBundle\Exceptions;

class ApiException extends \Exception
{

    protected $message = 'Problem with the API';

    public function __construct($exception, $code = 0)
    {
        parent::__construct($this->message, $code);
    }

}
