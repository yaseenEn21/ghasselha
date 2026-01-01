<?php

namespace App\Repositories\Eloquent;

use App\Models\Insurance;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\InsuranceRepositoryInterface;

class InsuranceRepository implements InsuranceRepositoryInterface
{
    public function query(): Builder
    {
        return Insurance::query();
    }

    public function create(array $data): Insurance
    {
        return Insurance::create($data);
    }

    public function find(int $id): ?Insurance
    {
        return Insurance::find($id);
    }

    public function update(int $id, array $data): bool
    {
        $c = $this->find($id);
        return $c ? $c->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $c = $this->find($id);
        return $c ? (bool) $c->delete() : false;
    }
}