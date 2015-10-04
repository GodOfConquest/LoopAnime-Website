<?php

namespace LoopAnime\UsersBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseHandler;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use LoopAnime\UsersBundle\Event\UserCreatedEvent;
use LoopAnime\UsersBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;

class RegistrationFormHandler extends BaseHandler {

    public function __construct(FormInterface $form, Request $request, UserManagerInterface $userManager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->form = $form;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->em = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param \FOS\UserBundle\Model\UserInterface|\LoopAnime\UsersBundle\Entity\Users $user
     * @param boolean $confirmation
     */
    protected function onSuccess(UserInterface $user, $confirmation)
    {
        $user->setCreateTime(New \DateTime("now"));
        $user->setStatus(0);

        parent::onSuccess($user, $confirmation);

        // Dispatch the even User Created
        $userEvent = new UserCreatedEvent($user);
        $this->eventDispatcher->dispatch(UserEvents::USER_CREATE, $userEvent);
    }
}
