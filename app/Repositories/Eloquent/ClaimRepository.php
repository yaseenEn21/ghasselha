<?php

namespace App\Repositories\Eloquent;

use App\Models\Claim;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\ClaimRepositoryInterface;

class ClaimRepository implements ClaimRepositoryInterface
{
    public function query(): Builder
    {
        return Claim::query();
    }

    public function create(array $data): Claim
    {
        return Claim::create($data);
    }

    public function find(int $id): ?Claim
    {
        return Claim::find($id);
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