<?php

namespace LoopAnime\AdminBundle\Command;

use Doctrine\ORM\EntityManager;
use LoopAnime\AppBundle\BusCommand\Anime\CreateLink;
use LoopAnime\AppBundle\Crawler\Enum\AnimeHosterEnum;
use LoopAnime\AppBundle\Crawler\Enum\SerieHosterEnum;
use LoopAnime\AppBundle\Crawler\Guesser\GuesserInterface;
use LoopAnime\AppBundle\Crawler\Service\CrawlerService;
use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodesRepository;
use LoopAnime\ShowsBundle\Entity\AnimesRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateLinksCommand extends ContainerAwareCommand {

    /** @var OutputInterface */
    private $output;
    /** @var EntityManager */
    private $doctrine;
    private $importLogHandler;

    protected function configure()
    {
        $this
            ->setName('loopanime:admin:import:populate-links')
            ->setDescription('Populates links collection for the animes\' episodes')
            ->addArgument('hoster', null, InputArgument::REQUIRED, 'Hoster to look on. [anime44, anitube] ')
            ->addOption('anime', null, InputOption::VALUE_REQUIRED, 'Anime id to look for', null)
            ->addOption('continuing', null, InputOption::VALUE_NONE, 'Only Anime that is today continuing')
            ->addOption('todayAired', null, InputOption::VALUE_NONE, 'Only for today aired Episodes')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Look for all episodes, even the ones already populated.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hoster = strtolower($input->getArgument('hoster'));
        $anime  = $input->getOption('anime');
        $all    = $input->getOption('all');
        $this->initLogger();
        $this->output = $output;

        $criteria = [];
        if ($anime) {
            $criteria = ['id' => $anime];
            $this->output->writeln(sprintf('<question>Grabing the links for the Show with the ID: %s</question>', $anime));
        } else {
            $this->output->writeln('<question>Updating links for all Animes!</question>');
        }
        if ($all) {
            $this->output->writeln('<question>Populate links for all episodes</question>');
        }

        if (!AnimeHosterEnum::isValid($hoster) && !SerieHosterEnum::isValid($hoster)) {
            throw new \Exception("I dont have the hoster $hoster");
        }

        /** @var CrawlerService $crawlerService */
        $crawlerService = $this->getContainer()->get('crawler.service');
        $hoster = $crawlerService->getHoster($hoster);

        $this->doctrine = $this->getContainer()->get('doctrine');
        /** @var AnimesRepository $animesRepo */
        $animesRepo = $this->doctrine->getRepository("LoopAnimeShowsBundle:Animes");
        /** @var Animes[] $animeObj */
        $animeObj = $animesRepo->findBy($criteria);

        foreach ($animeObj as $anime) {
            /** @var AnimesEpisodesRepository $aEpisodesRepo */
            $aEpisodesRepo = $this->doctrine->getRepository('LoopAnime\ShowsBundle\Entity\AnimesEpisodes');
            /** @var AnimesEpisodes[] $episodes */
            $episodes = $aEpisodesRepo->getEpisodes2Update($anime->getId(), $hoster, $all);
            foreach ($episodes as $episode) {
                $this->output->writeln('[' . $anime->getId() . '] Crawling the episode ' . $episode->getSeason()->getSeason() . "X" . $episode->getEpisode() . ' title: ' . $episode->getEpisodeTitle());
                try {
                    $mirrors = $crawlerService->crawlEpisode($episode, $hoster->getName());
                    $command = new CreateLink($episode, $hoster, $mirrors, $this->output);
                    $this->getContainer()->get('command_bus')->handle($command);
                    $output->writeln("<info>Episode was found with 100 accuracy! Gathered a total of " . count($mirrors) . " Mirrors</info>");
                } catch (\Exception $e) {
                    $output->writeln("<comment>Crawler throwed an expcetion: " . $e->getMessage() . "</comment>");
                    $output->writeln($e->getTraceAsString());
                    $this->logCrawling($episode, $crawlerService->getLastGuesser());
                }
            }
        }

        fclose($this->importLogHandler);
    }

    private function initLogger()
    {
        $this->importLogHandler = fopen('/var/log/import.log', 'w+');
        fputcsv($this->importLogHandler, [
            'serie_id',
            'episode_id',
            'episode_title',
            'epsiode_season',
            'episode_number',
            'episode_absolute_number',
            'possible_titles',
            'possible_episode_titles',
            'rule_crawler_season',
            'rule_crawler_title',
            'rule_crawler_episode',
            'rule_crawler_reset',
            'rule_crawler_reset',
            'best_uri',
            'best_log',
            'best_percentage'
        ]);
    }

    private function logCrawling(AnimesEpisodes $episode, GuesserInterface $guesser)
    {
        fputcsv($this->importLogHandler, [
            $episode->getSeason()->getAnime()->getId(),
            $episode->getId(),
            $episode->getEpisodeTitle(),
            $episode->getSeason()->getSeason(),
            $episode->getEpisode(),
            $episode->getAbsoluteNumber(),
            implode(", ", $crawler->getPossibleTitlesMatchs()),
            implode(", ", $crawler->getPossibleEpisodesTitlesMatchs()),
            $crawler->getSeasonSettingsUsed() ? $crawler->getSeasonSettingsUsed()->getSeason() : 'n.a',
            $crawler->getSeasonSettingsUsed() ? $crawler->getSeasonSettingsUsed()->getAnimeTitle() : 'n.a',
            $crawler->getSeasonSettingsUsed() ? $crawler->getSeasonSettingsUsed()->getEpisodeTitle() : 'n.a',
            $crawler->getSeasonSettingsUsed() ? $crawler->getSeasonSettingsUsed()->getReset() ? 'yes' : 'no' : 'n.a',
            $crawler->getSeasonSettingsUsed() ? $crawler->getSeasonSettingsUsed()->getHandicap() : 'n.a',
            $guesser->getUri(),
            $guesser->getLog(),
            $guesser->getCompPercentage()
        ]);
    }

}
