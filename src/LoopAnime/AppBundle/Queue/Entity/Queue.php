<?php

namespace LoopAnime\AppBundle\Queue\Entity;

use Doctrine\ORM\Mapping as ORM;
use LoopAnime\AppBundle\Queue\Enum\QueueStatus;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Countries
 *
 * @ORM\Table("queue")
 * @ORM\Entity(repositoryClass="LoopAnime\AppBundle\Queue\Entity\QueueRepository")
 */
class Queue
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=10)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="create_time", type="datetime")
     */
    private $createTime;

    /**
     * @var string
     *
     * @ORM\Column(name="process_time", type="datetime", nullable=true)
     */
    private $processTime;

    /**
     * @var string
     *
     * @ORM\Column(name="complete_time", type="datetime", nullable=true)
     */
    private $completeTime;

    /**
     * @var array
     *
     * @ORM\Column(name="data", type="array", length=900)
     */
    private $data;

    public function __construct()
    {
        $this->status = QueueStatus::PENDING;
        $this->processTime = null;
        $this->data = [];
        $this->createTime = new \DateTime('now');
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * @param \DateTime $createTime
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }

    /**
     * @return string
     */
    public function getProcessTime()
    {
        return $this->processTime;
    }

    /**
     * @param \DateTime $processTime
     */
    public function setProcessTime($processTime)
    {
        $this->processTime = $processTime;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @param \DateTime $param
     */
    public function setCompleteTime($param)
    {
        $this->completeTime = $param;
    }

    public function resetQueue()
    {
        $this->processTime = null;
        $this->completeTime = null;
        $this->status = QueueStatus::PENDING;
    }

}
