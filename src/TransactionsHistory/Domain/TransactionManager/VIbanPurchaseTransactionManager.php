<?php

declare(strict_types=1);

namespace App\TransactionsHistory\Domain\TransactionManager;

use App\TransactionsHistory\Domain\Transfer\Transaction;

final class VIbanPurchaseTransactionManager implements TransactionManagerInterface
{
    /**
     * @return array<string,array<string,mixed>>
     */
    public function manageTransactions(Transaction ...$transactions): array
    {
        $result = [];

        foreach ($transactions as $transaction) {
            $result[$transaction->getToCurrency()] ??= [
                'total' => 0,
                'totalInEuros' => 0,
            ];

            $result[$transaction->getToCurrency()]['total'] += $transaction->getToAmount();
            $result[$transaction->getToCurrency()]['totalInEuros'] += $transaction->getNativeAmount();
        }

        return $result;
    }
}
