<?php

declare(strict_types=1);

/**
 * Одна банковская транзакция.
 *
 * Все свойства приватные и задаются только через конструктор,
 * поэтому после создания объект изменить нельзя.
 */
class Transaction
{
    /**
     * @param int      $id          Уникальный идентификатор транзакции.
     * @param DateTime $date        Дата транзакции.
     * @param float    $amount      Сумма транзакции.
     * @param string   $description Описание платежа.
     * @param Merchant $merchant    Получатель платежа.
     */
    public function __construct(
        private int $id,
        private DateTime $date,
        private float $amount,
        private string $description,
        private Merchant $merchant
    ) {
    }

    /**
     * Возвращает идентификатор транзакции.
     *
     * @return int Идентификатор транзакции.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Возвращает дату транзакции.
     *
     * Возвращается копия объекта, чтобы дату нельзя было изменить снаружи
     * (DateTime — изменяемый объект).
     *
     * @return DateTime Дата транзакции.
     */
    public function getDate(): DateTime
    {
        return clone $this->date;
    }

    /**
     * Возвращает сумму транзакции.
     *
     * @return float Сумма транзакции.
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Возвращает описание платежа.
     *
     * @return string Описание платежа.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Возвращает получателя платежа.
     *
     * @return Merchant Получатель платежа.
     */
    public function getMerchant(): Merchant
    {
        return $this->merchant;
    }

    /**
     * Вычисляет количество полных дней, прошедших с момента транзакции до текущей даты.
     *
     * @return int Количество дней с момента транзакции.
     */
    public function getDaysSinceTransaction(): int
    {
        $now = new DateTime();
        return $this->date->diff($now)->days;
    }
}
