<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function baseQuery(): Builder
    {
        return $this->query()
            ->with('items.product')
            ->latest();
    }

    public function listForUser(int $userId, bool $isAdmin): Collection
    {
        $q = $this->baseQuery();

        if (!$isAdmin) {
            $q->where('user_id', $userId);
        }

        return $q->get();
    }
}
