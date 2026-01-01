<?php

namespace App\Repositories\Eloquent;

use App\Models\GarageAccount;
use App\Repositories\Contracts\GarageAccountRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class GarageAccountRepository implements GarageAccountRepositoryInterface
{
    public function query(): Builder
    {
        return GarageAccount::query();
    }

    public function forGarage(int $garageId): Builder
    {
        return $this->query()->where('garage_id', $garageId);
    }

    public function create(array $data): GarageAccount
    {
        return GarageAccount::create($data);
    }

    public function find(int $id): ?GarageAccount
    {
        return GarageAccount::find($id);
    }

    public function update(int $id, array $data): bool
    {
        $account = $this->find($id);
        return $account ? $account->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $account = $this->find($id);
        return $account ? (bool) $account->delete() : false;
    }
}
