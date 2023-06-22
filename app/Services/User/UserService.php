<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\BaseService;
use App\Repositories\UserRepository;
use App\Data\User\UpdatePersonalData;

class UserService extends BaseService
{
    /**
     * Create a new User Service instance
     *
     * @param \App\Repositories\UserRepository $repository
     */
    public function __construct(private UserRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Update the logged-in user's data
     *
     * @param \App\Data\User\UpdatePersonalData $data
     *
     * @return \App\Models\User
     */
    public function updatePersonalData(UpdatePersonalData $data): User
    {
        /** @var \App\Models\User */
        $user = auth()->user();

        if (empty($filteredData = array_filter($data->all()))) return $user;

        if (!empty($data->email)) $user->markEmailAsUnverified();

        return $this->repository->update($filteredData, $user);
    }
}
