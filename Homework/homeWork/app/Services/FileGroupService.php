<?php
namespace App\Services;

use App\Repositories\FileGroupRepository;

class FileGroupService
{
    protected $fileGroupRepository;

    public function __construct(FileGroupRepository $fileGroupRepository)
    {
        $this->fileGroupRepository = $fileGroupRepository;
    }

    public function createGroup(array $data, $ownerId)
    {
        $data['owner_id'] = $ownerId;
        return $this->fileGroupRepository->create($data);
    }

    public function inviteMember($groupId, $userId)
    {
        $this->fileGroupRepository->addMember($groupId, $userId);
    }
}
