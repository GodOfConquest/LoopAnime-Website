<?php

namespace LoopAnime\AppBundle\Queue\ConsoleCommand\Creators;

use Doctrine\ORM\EntityManager;
use LoopAnime\AppBundle\Queue\Entity\QueueRepository;
use LoopAnime\AppBundle\Queue\Enum\QueueType;
use LoopAnime\AppBundle\Queue\Services\QueueService;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodesRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateTodaysEpisodesConsoleCommand extends ContainerAwareCommand
{

    private $input;
    /** @var OutputInterface */
    private $output;
    /** @var \DateTime */
    private $startTime;
    /** @var EntityManager */
    private $em;
    /** @var QueueRepository */
    private $queueRepo;

    public function configure()
    {
        $this
            ->setName('la:queue:create:populate-episodes')
            ->setDescription('Create a queue entry for populate todays episodes')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Specify a date (format: Y-m-d)')
        ;
    }

    public function execute(InputInterface $inputInterface, OutputInterface $outputInterface)
    {
        $this->input = $inputInterface;
        $this->output = $outputInterface;
        $today = new \DateTime('now');
        $this->em = $this->getContainer()->get('doctrine');

        if ($inputInterface->hasOption('date') && !empty($inputInterface->getOption('date'))) {
            $today = \DateTime::createFromFormat('Y-m-d', $inputInterface->getOption('date'));
        }

        /** @var AnimesEpisodesRepository $episodesRepo */
        $episodesRepo = $this->em->getRepository('LoopAnimeShowsBundle:AnimesEpisodes');

        /** @var QueueService $queueService */
        $queueService = $this->getContainer()->get('queue.service');

        $this->output->writeln('Looking for episodes which are going to be aired at: ' . $today->format('Y-m-d h:i:s'));
        $episodes = $episodesRepo->getEpisodesByAirDate($today);
        $this->output->writeln('Found ' . count($episodes) . ' episodes.. Creating the queues');
        foreach ($episodes as $episode) {
            $this->output->writeln('Adding the episode ' . $episode->getId() . ' to the queue');
            $data = ['idEpisode' => $episode->getId()];
            $queueService->createJob(QueueType::POPULATE_EPISODE, $data);
        }
    }

}
