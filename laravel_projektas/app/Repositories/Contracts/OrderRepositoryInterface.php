<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function baseQuery(): Builder;

    /** Admin – visi, user – tik savo */
    public function listForUser(int $userId, bool $isAdmin): Collection;
}
