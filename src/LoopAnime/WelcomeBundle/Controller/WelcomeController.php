<?php

namespace LoopAnime\WelcomeBundle\Controller;

use LoopAnime\WelcomeBundle\Entity\Newsletter;
use LoopAnime\WelcomeBundle\Entity\NewsletterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class WelcomeController extends Controller {

    public function indexAction()
    {
        return $this->render("LoopAnimeWelcomeBundle:welcome:index.html.twig");
    }

    public function subscribeAction(Request $request)
    {
        /** @var NewsletterRepository $newsRepo */
        $newsRepo = $this->getDoctrine()->getRepository('LoopAnime\WelcomeBundle\Entity\Newsletter');

        if (!$request->get('EMAIL')) {
            return new JsonResponse(["result" => false, "msg"=>"Please submit a valid email!"]);
        }

        $record = $newsRepo->findOneBy(['email' => $request->get('EMAIL')]);
        if ($record === null) {
            $newEntry = new Newsletter();
            $newEntry->setEmail($request->get('EMAIL'));
            $newEntry->setStatus('1');
            $newEntry->setCreateTime(new \DateTime("now"));
            $em = $this->getDoctrine()->getManager();
            $em->persist($newEntry);
            $em->flush();
            return new JsonResponse(["result" => true, "msg" => "Awesome! Soon you will be able to use our website, stay tunned!"]);
        }
        return new JsonResponse(['result'=>false, "msg"=>"This email already exists, we will send you an invite as soon as possible!"]);
    }

    public function contactAction(Request $request)
    {
        $name = $request->get('name');
        $email = $request->get('email');
        $message = $request->get('message');

        $message = \Swift_Message::newInstance()
            ->setSubject('Webform - Contact us - Loop Anime')
            ->setFrom($email)
            ->setTo('webmaster@loop-anime.com')
            ->setBody('From: ' . $name . '\n\n' . $message)
        ;
        $this->get('mailer')->send($message);

        return new JsonResponse(['result' => true, "msg" => "Your email was sent successfully."]);
    }

}
