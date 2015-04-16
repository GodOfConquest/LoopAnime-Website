<?php

namespace LoopAnime\UsersBundle\Command;

use Doctrine\ORM\EntityManager;
use LoopAnime\UsersBundle\Entity\Invitation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateInvitationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('loopanimeusersbundle:invitations:user:create')
            ->setDescription('Creates a new invitation code')
            ->addArgument('email',InputArgument::REQUIRED,'Email to send the code')
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info>command creates a new invitation code.

<info>php %command.full_name%</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');

        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $invitationRepo = $entityManager->getRepository('LoopAnimeUsersBundle:Invitation');

        $invitation = $invitationRepo->findOneBy(['email' => $email]);
        if ($invitation) {
            $userRepo = $entityManager->getRepository('LoopAnimeUsersBundle:Users');
            $user = $userRepo->findOneBy(['invitation' => $invitation]);
            if ($user) {
                throw new \Exception("The user already used his invitation - nothing to do here.");
            }
            $output->writeln('<comment>The email you are trying to create a code already exist. Generating a new code<comment>');
            $invitation->resetInvitation();
        } else {
            $invitation = new Invitation();
        }
        $invitation->setEmail($email);
        $output->writeln('<comment>New Code '.$invitation->getCode().' was generated for the follow email: ' . $email .'</comment>');

        $entityManager->persist($invitation);
        $entityManager->flush();
    }
}
