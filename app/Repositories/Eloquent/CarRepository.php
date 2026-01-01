<?php

namespace App\Repositories\Eloquent;

use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\CarRepositoryInterface;

class CarRepository implements CarRepositoryInterface
{
    public function query(): Builder
    {
        return Car::query();
    }

    public function create(array $data): Car
    {
        return Car::create($data);
    }

    public function find(int $id): ?Car
    {
        return Car::find($id);
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
