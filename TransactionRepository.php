<?php

declare(strict_types=1);

/**
 * Хранилище транзакций в памяти.
 *
 * Отвечает только за хранение данных и базовые операции доступа к ним.
 * Транзакции хранятся в приватном массиве, ключ которого — id транзакции.
 */
class TransactionRepository implements TransactionStorageInterface
{
    /**
     * Транзакции, проиндексированные по id.
     *
     * @var array<int, Transaction>
     */
    private array $transactions = [];

    /**
     * Добавляет транзакцию в хранилище.
     *
     * @param Transaction $transaction Добавляемая транзакция.
     * @return void
     * @throws InvalidArgumentException Если транзакция с таким id уже существует.
     */
    public function addTransaction(Transaction $transaction): void
    {
        $id = $transaction->getId();

        if (isset($this->transactions[$id])) {
            throw new InvalidArgumentException("Транзакция с id {$id} уже существует.");
        }

        $this->transactions[$id] = $transaction;
    }

    /**
     * Удаляет транзакцию по идентификатору.
     * Если транзакции с таким id нет, ничего не происходит.
     *
     * @param int $id Идентификатор транзакции.
     * @return void
     */
    public function removeTransactionById(int $id): void
    {
        unset($this->transactions[$id]);
    }

    /**
     * Возвращает список всех транзакций.
     * Возвращается копия массива, поэтому изменить внутреннее хранилище через неё нельзя.
     *
     * @return Transaction[] Массив транзакций.
     */
    public function getAllTransactions(): array
    {
        return array_values($this->transactions);
    }

    /**
     * Ищет транзакцию по идентификатору.
     *
     * @param int $id Идентификатор транзакции.
     * @return Transaction|null Найденная транзакция или null, если её нет.
     */
    public function findById(int $id): ?Transaction
    {
        return $this->transactions[$id] ?? null;
    }
}
