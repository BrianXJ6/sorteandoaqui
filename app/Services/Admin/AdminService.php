<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Services\BaseService;
use App\Repositories\AdminRepository;
use App\Data\Admin\UpdatePersonalData;

class AdminService extends BaseService
{
    /**
     * Create a new Admin Service instance
     *
     * @param \App\Repositories\AdminRepository $repository
     */
    public function __construct(private AdminRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Update the logged-in admin's data
     *
     * @param \App\Data\Admin\UpdatePersonalData $data
     *
     * @return \App\Models\Admin
     */
    public function updatePersonalData(UpdatePersonalData $data): Admin
    {
        /** @var \App\Models\Admin */
        $admin = auth()->user();

        if (empty($filteredData = array_filter($data->all()))) return $admin;

        return $this->repository->update($filteredData, $admin);
    }
}
