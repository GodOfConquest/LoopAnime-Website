<?php

namespace LoopAnime\UsersBundle\Security\Core\User;

use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use LoopAnime\UsersBundle\Entity\Users;
use LoopAnime\UsersBundle\Event\UserConnectEvent;
use LoopAnime\UsersBundle\Event\UserCreatedEvent;
use LoopAnime\UsersBundle\Security\Core\User\Exception\ResourceOwnerUndeclaredException;
use LoopAnime\UsersBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class FOSUBUserProvider extends BaseClass
{

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager FOSUB user provider.
     * @param array $properties Property mapping.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(UserManagerInterface $userManager, array $properties, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($userManager, $properties);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        parent::connect($user, $response);

        $property = $this->getProperty($response);
        $username = $response->getUsername();

        $userConnectEvent = new UserConnectEvent($this->userManager, $user, $response);
        $this->eventDispatcher->dispatch(UserEvents::USER_CONNECT, $userConnectEvent);

        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();

        $setter = 'set' . ucfirst($service);
        $setter_id = $setter . 'Id';
        $setter_token = $setter . 'AccessToken';

        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }

        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $service = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($service);
        $setter_id = $setter . 'Id';
        $setter_token = $setter . 'AccessToken';

        /** @var GoogleResourceOwner $resourceOwner */
        $resourceOwner = $response->getResourceOwner();
        $responseData = $response->getResponse();

        $createTime = new \DateTime("now");
        $country = "UK";
        $birthday = new \DateTime("now");
        if (isset($responseData['birthday']))
            $birthday = new \DateTime($responseData['birthday']);

        switch ($resourceOwner->getName()) {
            case "google":
                $avatar = $response->getProfilePicture();
                if (!empty(explode("-", $responseData['locale'])[1]))
                    $country = explode("-", $responseData['locale'])[1];
                break;
            case "facebook":
                $avatar = $response->getProfilePicture();
                if (!empty(explode("_", $responseData['locale'])[1]))
                    $country = explode("_", $responseData['locale'])[1];
                break;
            default:
                throw new ResourceOwnerUndeclaredException($resourceOwner->getName());
        }

        $username = $response->getUsername();
        /** @var Users $user */
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));
        if (null === $user) {
            $user = $this->userManager->findUserByEmail($response->getEmail());
        }

        if (null === $user) {
            $user = $this->userManager->createUser();
            $user->setUsername($response->getUsername());
            $user->setEmail($response->getEmail());
            $user->setPassword(sha1(time()));
            $user->setBirthdate($birthday);
            $user->setCountry($country);
            $user->setCreateTime($createTime);
            $user->setLang("ENG");
            $user->setNewsletter(1);
            $user->setStatus(1);
            $user->setEnabled(true);
            $this->userManager->updateUser($user);

            $userEvent = new UserCreatedEvent($user);
            $this->eventDispatcher->dispatch(UserEvents::USER_CREATE, $userEvent);
        }

        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        if (empty($user->getAvatar())) {
            $user->setAvatar($avatar);
        }
        $this->userManager->updateUser($user);

        return $user;
    }

}
