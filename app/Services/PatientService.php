<?php

namespace App\Services;

use App\Repositories\PatientRepository;

class PatientService
{
    private $repo;

    public function __construct(PatientRepository $repo)
    {
        $this->repo = $repo;
    }

    public function listPatients()
    {
        return $this->repo->getAll();
    }
}
