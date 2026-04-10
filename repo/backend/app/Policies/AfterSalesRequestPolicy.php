<?php

namespace App\Policies;

use App\Models\AfterSalesRequest;
use App\Models\User;

class AfterSalesRequestPolicy
{
    /**
     * Staff with after_sales.resolve may review an after-sales request.
     */
    public function review(User $user, AfterSalesRequest $request): bool
    {
        return $user->hasPermission('after_sales.resolve');
    }

    /**
     * Staff with after_sales.resolve may resolve an after-sales request.
     */
    public function resolve(User $user, AfterSalesRequest $request): bool
    {
        return $user->hasPermission('after_sales.resolve');
    }
}
