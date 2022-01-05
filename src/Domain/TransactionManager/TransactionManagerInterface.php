<?php

declare(strict_types=1);

namespace App\Domain\TransactionManager;

use App\Domain\Transaction;

interface TransactionManagerInterface
{
    public function manageTransactions(Transaction ...$transactions): array;
}