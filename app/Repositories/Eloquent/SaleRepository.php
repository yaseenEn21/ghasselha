<?php

namespace App\Repositories\Eloquent;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\SaleRepositoryInterface;

class SaleRepository implements SaleRepositoryInterface
{
    public function query(): Builder
    {
        return Sale::query();
    }

    public function create(array $data): Sale
    {
        return Sale::create($data);
    }

    public function find(int $id): ?Sale
    {
        return Sale::find($id);
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