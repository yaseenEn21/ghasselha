<?php

namespace App\Repositories\Eloquent;

use App\Models\Commitment;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\CommitmentRepositoryInterface;

class CommitmentRepository implements CommitmentRepositoryInterface
{
    public function query(): Builder
    {
        return Commitment::query();
    }

    public function create(array $data): Commitment
    {
        return Commitment::create($data);
    }

    public function find(int $id): ?Commitment
    {
        return Commitment::find($id);
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
