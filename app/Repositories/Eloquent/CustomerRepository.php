<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\CustomerRepositoryInterface;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function query(): Builder
    {
        return Customer::query();
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function find(int $id): ?Customer
    {
        return Customer::find($id);
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
