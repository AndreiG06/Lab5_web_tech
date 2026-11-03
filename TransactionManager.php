<?php

declare(strict_types=1);

/**
 * Бизнес-логика над коллекцией транзакций: вычисления и сортировки.
 *
 * Сам транзакции не создаёт и не хранит — все данные получает из хранилища,
 * переданного через конструктор.
 */
class TransactionManager
{
    /**
     * @param TransactionStorageInterface $repository Хранилище транзакций.
     */
    public function __construct(
        private TransactionStorageInterface $repository
    ) {
    }

    /**
     * Вычисляет общую сумму всех транзакций.
     *
     * @return float Общая сумма.
     */
    public function calculateTotalAmount(): float
    {
        return $this->sumAmounts($this->repository->getAllTransactions());
    }

    /**
     * Вычисляет сумму транзакций за период (границы включаются).
     *
     * @param string $startDate Начало периода в формате Y-m-d.
     * @param string $endDate   Конец периода в формате Y-m-d.
     * @return float Сумма транзакций за период.
     * @throws InvalidArgumentException Если дата задана в неверном формате.
     */
    public function calculateTotalAmountByDateRange(string $startDate, string $endDate): float
    {
        $start = $this->parseDate($startDate)->setTime(0, 0, 0);
        $end = $this->parseDate($endDate)->setTime(23, 59, 59);

        $filtered = array_filter(
            $this->repository->getAllTransactions(),
            fn(Transaction $t): bool => $t->getDate() >= $start && $t->getDate() <= $end
        );

        return $this->sumAmounts($filtered);
    }

    /**
     * Подсчитывает количество транзакций по получателю (без учёта регистра).
     *
     * @param string $merchant Название получателя.
     * @return int Количество транзакций.
     */
    public function countTransactionsByMerchant(string $merchant): int
    {
        $count = 0;

        foreach ($this->repository->getAllTransactions() as $transaction) {
            if (strcasecmp($transaction->getMerchant()->getName(), $merchant) === 0) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Сортирует транзакции по дате (от старых к новым).
     *
     * @return Transaction[] Отсортированный массив транзакций.
     */
    public function sortTransactionsByDate(): array
    {
        $transactions = $this->repository->getAllTransactions();
        usort($transactions, fn(Transaction $a, Transaction $b): int => $a->getDate() <=> $b->getDate());

        return $transactions;
    }

    /**
     * Сортирует транзакции по сумме по убыванию.
     *
     * @return Transaction[] Отсортированный массив транзакций.
     */
    public function sortTransactionsByAmountDesc(): array
    {
        $transactions = $this->repository->getAllTransactions();
        usort($transactions, fn(Transaction $a, Transaction $b): int => $b->getAmount() <=> $a->getAmount());

        return $transactions;
    }

    /**
     * Суммирует суммы переданных транзакций.
     *
     * @param Transaction[] $transactions Массив транзакций.
     * @return float Сумма.
     */
    private function sumAmounts(array $transactions): float
    {
        $total = 0.0;

        foreach ($transactions as $transaction) {
            $total += $transaction->getAmount();
        }

        return $total;
    }

    /**
     * Преобразует строку формата Y-m-d в объект DateTime.
     *
     * @param string $date Дата в формате Y-m-d.
     * @return DateTime Объект даты.
     * @throws InvalidArgumentException Если строка не является корректной датой.
     */
    private function parseDate(string $date): DateTime
    {
        $result = DateTime::createFromFormat('Y-m-d', $date);

        if ($result === false || $result->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException("Некорректная дата: {$date}. Ожидается формат Y-m-d.");
        }

        return $result;
    }
}
