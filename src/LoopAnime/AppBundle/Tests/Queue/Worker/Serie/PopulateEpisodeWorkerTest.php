<?php

namespace LoopAnime\AppBundle\Tests\Queue\Worker\Serie;

use LoopAnime\AppBundle\Enum\TypeSerieEnum;
use LoopAnime\AppBundle\Queue\Entity\Queue;
use LoopAnime\AppBundle\Queue\Worker\BaseWorker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PopulateEpisodeWorkerTest extends KernelTestCase
{
    /** @var BaseWorker */
    private $worker;

    public function setUp()
    {
        parent::setUp();
        self::bootKernel();
        $this->worker = self::$kernel->getContainer()->get('queue.worker.populate');
        $this->worker->setEntityManager(self::$kernel->getContainer()->get('doctrine.orm.entity_manager'));
        $this->worker->setContainer(self::$kernel->getContainer());

        $job = new Queue();
        $job->setData(['idEpisode' => 1, 'type' => TypeSerieEnum::ANIME]);
        $this->worker->setJob($job);
    }

    /**
     * @test
     */
    public function is_valid_job()
    {
        $isValid = $this->worker->validate();

        $this->assertEquals(true, $isValid);
    }

    /**
     * @test
     * @expectedException \LoopAnime\AppBundle\Queue\Exception\WorkerDataMalformedException
     */
    public function is_not_valid_job()
    {
        $this->worker->setJob(new Queue());
        $this->worker->validate();
    }

    public function worker_can_Work()
    {
        $this->worker->runWorker();
    }
}
