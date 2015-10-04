<?php

namespace LoopAnime\AdminBundle\Command;

use LoopAnime\ShowsAPIBundle\Entity\AnimesAPI;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateAnimeCommand extends ContainerAwareCommand {

    /** @var OutputInterface */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('loopanime:admin:import:update-animes')
            ->setDescription('Updates Animes on the database')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Updates all Animes , including the ones that have already finish')
            ->addOption('anime', 'i', InputOption::VALUE_REQUIRED, 'Updates a specific Anime - Provide the ID of the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isAll = $input->getOption('all');
        $anime = $input->getOption('anime');
        $this->output = $output;

        if ($isAll) {
            $this->output->writeln('<question>Updating All Animes!</question>');
        }
        if ($anime) {
            $this->output->writeln('<question>Updating only the anime with the ID: ' . $anime . '</question>');
        }

        $doctrine = $this->getContainer()->get('doctrine');
        /** @var AnimesAPI[] $animes */
        $animes = $doctrine->getRepository('LoopAnimeShowsAPIBundle:AnimesAPI')->getAnimesToUpdate($isAll, $anime);

        foreach ($animes as $anime) {
            $this->output->writeln('<error>Failed to update the anime ' . $anime->getApiAnimeKey() . '</error>');
            $command = $this->getApplication()->find('loopanime:admin:import:add-anime');
            $arguments = ['--tvdbId' => $anime->getApiAnimeKey()];
            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $output);
            if ($returnCode !== 0) {
                $this->output->writeln('<error>Failed to update the anime ' . $anime->getApiAnimeKey() . '</error>');
            }
        }
        $this->output->writeln('<info>All Animes were Updated!</info>');
    }

}
