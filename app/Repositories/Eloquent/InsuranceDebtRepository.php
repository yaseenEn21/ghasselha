<?php

namespace App\Repositories\Eloquent;

use App\Models\InsuranceDebt;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\InsuranceDebtRepositoryInterface;

class InsuranceDebtRepository implements InsuranceDebtRepositoryInterface
{
    public function query(): Builder
    {
        return InsuranceDebt::query();
    }

    public function create(array $data): InsuranceDebt
    {
        return InsuranceDebt::create($data);
    }

    public function find(int $id): ?InsuranceDebt
    {
        return InsuranceDebt::find($id);
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