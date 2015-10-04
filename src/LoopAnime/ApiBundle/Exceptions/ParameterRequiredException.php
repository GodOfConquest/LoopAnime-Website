<?php
namespace LoopAnime\ApiBundle\Exceptions;

class ParameterRequiredException extends \BadMethodCallException
{

    protected $message = 'The follow parameters are required';

    public function __construct($parameters, $code = 400)
    {
        parent::__construct($this->message . implode(",", $parameters), $code);
    }

}
