<?php

namespace App\Repositories;

use App\Models\Patient;

class PatientRepository
{
    public function getAll()
    {
        return Patient::all();
    }

    public function findById($id)
    {
        return Patient::find($id);
    }

    public function create(array $data)
    {
        return Patient::create($data);
    }

    public function update($id, array $data)
    {
        $patient = Patient::find($id);
        if (!$patient) return null;

        $patient->update($data);
        return $patient;
    }

    public function delete($id)
    {
        return Patient::destroy($id);
    }
}
