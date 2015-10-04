<?php
namespace LoopAnime\ApiBundle\Exceptions;

class ResourceNotFoundException extends \InvalidArgumentException
{

    protected $message = 'Your resource %s does not exist or was not found!';

    public function __construct($resource, $code = 404)
    {
        if (is_object($resource)) {
            $resource = get_class($resource);
        }
        parent::__construct(sprintf($this->message, $resource), $code);
    }

}
