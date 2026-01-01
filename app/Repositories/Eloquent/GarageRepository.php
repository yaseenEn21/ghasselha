<?php

namespace App\Repositories\Eloquent;

use App\Models\Garage;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\GarageRepositoryInterface;

class GarageRepository implements GarageRepositoryInterface
{
    public function query(): Builder
    {
        return Garage::query();
    }

    public function create(array $data): Garage
    {
        return Garage::create($data);
    }

    public function find(int $id): ?Garage
    {
        return Garage::find($id);
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
