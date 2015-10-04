<?php

namespace LoopAnime\UsersBundle\Command;

use Doctrine\ORM\EntityManager;
use LoopAnime\AppBundle\Sync\Enum\SyncEnum;
use LoopAnime\AppBundle\Sync\Services\SyncService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('loopanimeusersbundle:sync:user')
            ->setDescription('Synchronize a user with an network')
            ->addArgument('user', InputArgument::REQUIRED, 'User ID to synchronize')
            ->addOption('network', 't', InputOption::VALUE_REQUIRED, 'Network to sync with')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $input->getArgument('user');
        $network = $input->getOption('network');

        /** @var SyncService $syncService */
        $syncService = $this->getContainer()->get('sync.service');
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entityManager->getRepository('LoopAnimeUsersBundle:Users')->find($user);
        if (!$user) {
            throw new \Exception(sprintf('User %s not found!', $user));
        }

        if (!empty($user->getTraktAccessToken()) && (empty($network) || $network === SyncEnum::SYNC_TRAKT)) {
            $output->writeln('<comment>Importing User ' . $user->getUsername() . ' Trakt\'s Account</comment>');
            $syncService->importSeenEpisodes($user, SyncEnum::SYNC_TRAKT);
        }
        if (!empty($user->getMALUsername()) && (empty($network) || $network === SyncEnum::SYNC_MAL)) {
            $output->writeln('<comment>Importing User ' . $user->getUsername() . ' MAL\'s Account</comment>');
            $syncService->importSeenEpisodes($user, SyncEnum::SYNC_MAL);
        }

        $output->writeln('Sync has now finished!');
        return 0;
    }

}
