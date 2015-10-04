<?php
namespace LoopAnime\AppBundle\Queue\Worker\User;


use LoopAnime\AppBundle\Queue\Enum\QueueType;
use LoopAnime\AppBundle\Queue\Exception\SubjectNotFoundException;
use LoopAnime\AppBundle\Queue\Exception\WorkerDataMalformedException;
use LoopAnime\AppBundle\Queue\Worker\BaseWorker;
use LoopAnime\AppBundle\Queue\Worker\WorkerInterface;
use LoopAnime\AppBundle\Sync\Enum\SyncEnum;
use LoopAnime\AppBundle\Sync\Services\SyncService;
use LoopAnime\UsersBundle\Entity\Users;

class SyncUserWorker extends BaseWorker implements WorkerInterface
{

    public function runWorker()
    {
        $data = $this->getData();
        $userId = $data['userId'];
        $userRepo = $this->em->getRepository('LoopAnimeUsersBundle:Users');
        /** @var Users $user */
        $user = $userRepo->find($userId);
        if (!$user) {
            throw new SubjectNotFoundException('User ' . $userId . ' is not valid or was not found!');
        }

        /** @var SyncService $syncService */
        $syncService = $this->getContainer()->get('sync.service');

        if (!empty($user->getTraktAccessToken()) && (empty($network) || $network === SyncEnum::SYNC_TRAKT)) {
            $this->log('Importing User ' . $user->getId() . ' Trakt\'s Account', 'comment');
            $syncService->importSeenEpisodes($user, SyncEnum::SYNC_TRAKT);
        }
        if (!empty($user->getMALUsername()) && (empty($network) || $network === SyncEnum::SYNC_MAL)) {
            $this->log('Importing User ' . $user->getId() . ' MAL\'s Account', 'comment');
            $syncService->importSeenEpisodes($user, SyncEnum::SYNC_MAL);
        }

        return true;
    }

    public function validate()
    {
        $data = $this->getData();
        $keys = [];
        if (empty($data['userId'])) {
            $keys[] = 'userId';
        }
        if (!empty($keys)) {
            throw new WorkerDataMalformedException($keys);
        }

        return true;
    }

    public function getQueueType()
    {
        return QueueType::SYNC_USER;
    }

}
