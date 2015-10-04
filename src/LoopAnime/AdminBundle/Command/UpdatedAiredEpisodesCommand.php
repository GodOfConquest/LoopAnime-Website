<?php

namespace LoopAnime\AdminBundle\Command;

use LoopAnime\AppBundle\BusCommand\Anime\CreateLink;
use LoopAnime\AppBundle\Crawler\Enum\AnimeHosterEnum;
use LoopAnime\AppBundle\Crawler\Hoster\HosterInterface;
use LoopAnime\AppBundle\Crawler\Service\CrawlerService;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodesRepository;
use LoopAnime\ShowsBundle\Entity\AnimesLinksRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatedAiredEpisodesCommand extends ContainerAwareCommand {

    /** @var OutputInterface */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('loopanime:admin:import:aired-episodes')
            ->setDescription('Updates Episodes that went for air today or near and didnt got updated yet.')
            ->addOption('date', null, InputOption::VALUE_REQUIRED, 'Add a Specific date to look for [format: Y-m-d]')
            ->addOption('hoster', null, InputOption::VALUE_REQUIRED, 'Hoster where to look for the new episodes')
            ->addOption('allEpisodes', null, InputOption::VALUE_NONE, 'Grabs for all episodes including the ones with links already!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $date = new \DateTime('now');
        $hosters = AnimeHosterEnum::getAsArray();
        $all = $input->getOption('allEpisodes');

        if ($input->hasOption('date') && !empty($input->getOption('date'))) {
            $date = \DateTime::createFromFormat('Y-m-d', $input->getOption('date'));
        }
        if ($input->hasOption('hoster') && !empty($input->getOption('hoster'))) {
            $hosters = [$input->getOption('hoster')];
        }
        if ($all) {
            $this->output->writeln('<comment>Grabbing all episodes (even the ones with links)</comment>');
        }

        foreach ($hosters as $hoster) {
            $this->output->writeln('Updating the anime from the hoster ' . $hoster);
            $doctrine = $this->getContainer()->get('doctrine');
            /** @var AnimesEpisodesRepository $aEpisodesRepo */
            $aEpisodesRepo = $doctrine->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes');

            $this->output->writeln(sprintf('<comment>Getting the episodes from the %s to the hoster %s</comment>', $date->format('Y-m-d'), $hoster));
            /** @var AnimesEpisodes[] $episodes */
            $episodes = $aEpisodesRepo->getEpisodesByAirDate($date, $hoster, null, $all);

            if (!$episodes) {
                $this->output->writeln('<info>There are no episodes for the date: ' . $date->format('Y-m-d') . ', and hoster:' . $hoster . '</info>');
                return 0;
            }

            $this->updateEpisodes($episodes, $hoster);
        }

        return true;
    }

    /**
     * @param AnimesEpisodes[] $episodes
     * @param $hoster
     */
    private function updateEpisodes($episodes, $hoster)
    {
        /** @var CrawlerService $crawlerService */
        $crawlerService = $this->getContainer()->get('crawler.service');

        /** @var HosterInterface $hoster */
        $hoster = $crawlerService->getHoster($hoster);
        $doctrine = $this->getContainer()->get('doctrine');

        /** @var AnimesLinksRepository $linksRepo */
        $linksRepo = $doctrine->getRepository('LoopAnimeShowsBundle:AnimesLinks');
        foreach ($episodes as $episode) {
            // Remove old links for that episode and hoster
            $numDelete = $linksRepo->removeLinks($hoster, $episode);
            if ($numDelete) {
                $this->output->writeln(sprintf('<comment>Removed %s links for the episode %s</comment>', $numDelete, $episode->getId()));
            }
            $this->output->writeln('crawling the episode ' . $episode->getSeason()->getSeason() . ':' . $episode->getEpisode() . " Absolute: " . $episode->getAbsoluteNumber() . ' title: ' . $episode->getEpisodeTitle());

            try {
                $mirrors = $crawlerService->crawlEpisode($episode, $hoster->getName());
                $command = new CreateLink($episode, $hoster, $mirrors, $this->output);
                $this->getContainer()->get('command_bus')->handle($command);
                $this->output->writeln("<info>Episode was found with 100 accuracy! Gathered a total of " . count($mirrors) . " Mirrors</info>");
            } catch (\Exception $e) {
                $this->output->writeln("<comment>Crawler throwed an expcetion: " . $e->getMessage() . "</comment>");
                $this->output->writeln($e->getTraceAsString());
                //$this->logCrawling($episode, ['uri' => '', 'log' => $e->getMessage(), 'percentage' => 0]);
            }


        }
    }

}
