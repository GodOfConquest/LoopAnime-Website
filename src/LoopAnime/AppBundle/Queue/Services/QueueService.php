<?php

namespace LoopAnime\AppBundle\Queue\Services;

use Doctrine\ORM\EntityManager;
use LoopAnime\AppBundle\Queue\Entity\Queue;
use LoopAnime\AppBundle\Queue\Enum\QueueStatus;
use LoopAnime\AppBundle\Queue\Enum\QueueType;
use LoopAnime\AppBundle\Queue\Exception\InvalidQueueTyeException;
use LoopAnime\AppBundle\Queue\Exception\WorkerNotDefinedException;
use LoopAnime\AppBundle\Queue\Worker\WorkerFactory;
use LoopAnime\AppBundle\Queue\Worker\WorkerInterface;

class QueueService
{

    /** @var EntityManager */
    private $em;
    /** @var WorkerInterface[] */
    private $workers;
    /** @var WorkerFactory */
    private $workerFactory;

    public function __construct(EntityManager $em, WorkerFactory $workerFactory)
    {
        $this->em = $em;
        $this->workerFactory = $workerFactory;
    }

    public function addWorker(WorkerInterface $worker)
    {
        $this->workers[$worker->getQueueType()] = $worker;
    }

    public function getWorker($type)
    {
        if (!isset($this->workers[$type])) {
            throw new WorkerNotDefinedException();
        }
        return $this->workers[$type];
    }

    /**
     * @param Queue $job
     * @return true|\Exception
     */
    public function validateJob(Queue $job)
    {
        $worker = $this->workerFactory->create($job);
        return $worker->validate();
    }

    public function createJob($type, array $data)
    {
        if (!QueueType::isValid($type)) {
            throw new InvalidQueueTyeException($type);
        }

        $queue = new Queue();
        $queue->setType($type);
        $queue->setData($data);
        $this->validateJob($queue);
        $this->em->persist($queue);
        $this->em->flush($queue);

        return $queue;
    }

    public function setCompleted(Queue $job)
    {
        $job->setStatus(QueueStatus::COMPLETED);
        $job->setCompleteTime(new \DateTime('now'));
        $this->em->persist($job);
        $this->em->flush($job);

        return $job;
    }

    public function setProcessing(Queue $job)
    {
        $job->setStatus(QueueStatus::PROCESSING);
        $job->setProcessTime(new \DateTime('now'));
        $this->em->persist($job);
        $this->em->flush($job);

        return $job;
    }

    public function setFailed(Queue $job)
    {
        $job->setStatus(QueueStatus::FAILED);
        $job->setCompleteTime(new \DateTime('now'));
        $this->em->persist($job);
        $this->em->flush($job);

        return $job;
    }

}
