<?php

declare(strict_types=1);

namespace App\TransactionsHistory\Domain\Service;

use App\TransactionsHistory\Domain\IO\FileReaderServiceInterface;
use App\TransactionsHistory\Domain\Mapper\TransactionMapperInterface;
use App\TransactionsHistory\Domain\Transfer\Transaction;
use App\TransactionsHistory\Domain\Transfer\TransactionAggregators;
use Safe\Exceptions\ArrayException;

use function Safe\ksort;

final class AggregateService
{
    private FileReaderServiceInterface $fileReaderService;

    private TransactionMapperInterface $transactionMapper;

    private TransactionAggregators $transactionAggregators;

    public function __construct(
        FileReaderServiceInterface $fileReaderService,
        TransactionMapperInterface $transactionMapper,
        TransactionAggregators $transactionAggregators
    ) {
        $this->fileReaderService = $fileReaderService;
        $this->transactionMapper = $transactionMapper;
        $this->transactionAggregators = $transactionAggregators;
    }

    /**
     * @throws ArrayException
     *
     * @return array<string,array<string,mixed>>
     */
    public function forFilepath(string $filepath): array
    {
        $csv = $this->fileReaderService->read($filepath);

        $groupedTransactions = $this->generateTransactionsGroupedByType($csv);

        return $this->aggregateTransactions($groupedTransactions);
    }

    /**
     * @param list<array<string,string>> $csv
     *
     * @return array<string,list<Transaction>>
     */
    private function generateTransactionsGroupedByType(array $csv): array
    {
        $transactions = array_map(
            fn(array $row): Transaction => $this->transactionMapper->map($row),
            $csv
        );

        $result = [];

        foreach ($transactions as $transaction) {
            $result[$transaction->getTransactionType()] ??= [];
            $result[$transaction->getTransactionType()][] = $transaction;
        }

        return $result;
    }

    /**
     * @param array<string,list<Transaction>> $groupedTransactions
     *
     * @throws ArrayException
     *
     * @return array<string,array<string,mixed>>
     */
    private function aggregateTransactions(array $groupedTransactions): array
    {
        $result = [];

        foreach ($groupedTransactions as $type => $transactions) {
            $currentAggregated = [];

            foreach ($this->transactionAggregators->getAll() as $aggregator) {
                $currentAggregated[] = $aggregator->aggregate(...$transactions);
            }
            $result[$type] = array_merge(...$currentAggregated);
        }

        ksort($result);

        return $result;
    }
}
