<?php

namespace LoopAnime\UsersBundle\Security\Core\User\Exception;


class ResourceOwnerUndeclaredException extends \Exception {

    public function __construct($resource, $code = 0) {
        parent::__construct(sprintf('Recource Owner %s is not declared', $resource), $code);
    }

}
