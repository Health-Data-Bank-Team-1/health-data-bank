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

    public function getPatient($id)
    {
        return $this->repo->findById($id);
    }

    public function createPatient($data)
    {
        return $this->repo->create($data);
    }

    public function updatePatient($id, $data)
    {
        return $this->repo->update($id, $data);
    }

    public function deletePatient($id)
    {
        return $this->repo->delete($id);
    }
}
