<?php

namespace App\Services;

use App\Repositories\MemberLogRepository;

class MemberLogService
{
    protected $memberLogRepository;

    public function __construct(MemberLogRepository $memberLogRepository)
    {
        $this->memberLogRepository = $memberLogRepository;
    }

    public function createMemberLog(array $data)
    {
        return $this->memberLogRepository->createMemberLog($data);
    }

    public function getMemberLogById($id)
    {
        return $this->memberLogRepository->findMemberLogById($id);
    }

    public function listMemberLogs()
    {
        return $this->memberLogRepository->getAllMemberLogs();
    }
}
