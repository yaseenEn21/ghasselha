<?php

namespace App\Repositories\Eloquent;

use App\Models\Accident;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\AccidentRepositoryInterface;

class AccidentRepository implements AccidentRepositoryInterface
{
    public function query(): Builder
    {
        return Accident::query();
    }

    public function create(array $data): Accident
    {
        return Accident::create($data);
    }

    public function find(int $id): ?Accident
    {
        return Accident::find($id);
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
