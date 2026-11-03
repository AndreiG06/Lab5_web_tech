<?php

declare(strict_types=1);

/**
 * Контракт хранилища транзакций.
 *
 * Описывает, какие операции должно поддерживать любое хранилище,
 * не определяя, где и как именно хранятся данные.
 */
interface TransactionStorageInterface
{
    /**
     * Добавляет транзакцию в хранилище.
     *
     * @param Transaction $transaction Добавляемая транзакция.
     * @return void
     */
    public function addTransaction(Transaction $transaction): void;

    /**
     * Удаляет транзакцию по идентификатору.
     *
     * @param int $id Идентификатор транзакции.
     * @return void
     */
    public function removeTransactionById(int $id): void;

    /**
     * Возвращает список всех транзакций.
     *
     * @return Transaction[] Массив транзакций.
     */
    public function getAllTransactions(): array;

    /**
     * Ищет транзакцию по идентификатору.
     *
     * @param int $id Идентификатор транзакции.
     * @return Transaction|null Найденная транзакция или null, если её нет.
     */
    public function findById(int $id): ?Transaction;
}
