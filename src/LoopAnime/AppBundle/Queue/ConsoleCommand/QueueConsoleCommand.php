<?php

namespace LoopAnime\AppBundle\Queue\ConsoleCommand;

use Doctrine\ORM\EntityManager;
use LoopAnime\AppBundle\Queue\Entity\QueueRepository;
use LoopAnime\AppBundle\Queue\Services\QueueService;
use LoopAnime\AppBundle\Queue\Worker\WorkerFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueConsoleCommand extends ContainerAwareCommand
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
    private $maxjobs;

    public function configure()
    {
        $this
            ->setName('la:queue:server-run')
            ->setDescription('Run the queue management server')
            ->addOption('maxjobs', null, InputOption::VALUE_REQUIRED, 'Max of Jobs that is possible to run at same time', 5)
        ;
    }

    public function execute(InputInterface $inputInterface, OutputInterface $outputInterface)
    {
        $this->input = $inputInterface;
        $this->output = $outputInterface;
        $this->startTime = new \DateTime('now');
        $this->maxjobs = !empty($this->input->getOption('maxjobs')) ? $this->input->getOption('maxjobs') : 5;

        $this->em = $this->getContainer()->get('doctrine');
        $this->queueRepo = $this->em->getRepository('LoopAnime\AppBundle\Queue\Entity\Queue');

        if ($this->canRun()) {
            $this->output->writeln('Queue Server - booting');
            $this->runServer();
        } else {
            $this->output->writeln('Queue Server - cant run at this time, there are jobs being processed');
        }
    }

    private function runServer()
    {
        $workerFactory = $this->getWorkerFactory();
        /** @var QueueService $queueService */
        $queueService = $this->getContainer()->get('queue.service');
        $jobs = $this->queueRepo->getPendingJobs(5);
        if ($jobs) {
            $this->output->writeln('Found ' . count($jobs) . '.. Start seting it to the workers.');
            foreach ($jobs as $job) {
                try {
                    $this->output->writeln(sprintf('Creating and setting a %s worker for the job with the id %s', $job->getType(), $job->getId()));
                    $worker = $workerFactory->create($job);
                    $worker->validate();
                    $queueService->setProcessing($job);
                    if ($worker->runWorker() === true) {
                        $this->output->writeln(sprintf('Job run without any problem marked as success!'));
                        $queueService->setCompleted($job);
                    } else {
                        $this->output->writeln(sprintf('Job run with problems set as failed!'));
                        $queueService->setFailed($job);
                    }

                    $this->output->writeln('<info>Worker has finished the job!</info>');
                } catch (\Exception $e) {
                    $this->output->writeln(sprintf('Job run with problems set as failed!'));
                    $queueService->setFailed($job);
                    $this->output->writeln('Error: ' . $e->getMessage());
                }
            }
        }

        $this->deleteOld();
    }

    /**
     * @return WorkerFactory
     */
    private function getWorkerFactory()
    {
        /** @var WorkerFactory $wokerFactory */
        $workerFactory = $this->getContainer()->get('queue.worker.factory');
        $workerFactory->setOutput($this->output);

        return $workerFactory;
    }

    private function deleteOld()
    {
        $validatePast = $this->startTime->add(new \DateInterval('P3D'));

        if ($validatePast < $this->startTime) {
            $this->output->writeln('<info>Removing old entries on the queue</info>');
            $q = $this->em->createQuery('DELETE FROM LoopAnimeAppBundle\Queue\Entity\Queue q where q.createTime <= :date')
                ->setParameter('date', $validatePast->format('Y-m-d'));
            $numDeleted = $q->execute();
            $this->output->writeln(sprintf('<info>%s Deleted successfully!</info>', $numDeleted));
        }
    }

    public function canRun()
    {
        $jobs = $this->queueRepo->getProcessingJobs();
        if (count($jobs) >= $this->maxjobs) {
            return false;
        }

        return true;
    }

}
