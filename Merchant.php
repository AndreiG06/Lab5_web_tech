<?php

declare(strict_types=1);

/**
 * Получатель платежа (магазин, сервис, организация).
 */
class Merchant
{
    /**
     * @param string $name     Название получателя.
     * @param string $category Категория получателя (например, «Продукты», «Транспорт»).
     */
    public function __construct(
        private string $name,
        private string $category
    ) {
    }

    /**
     * Возвращает название получателя.
     *
     * @return string Название получателя.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Возвращает категорию получателя.
     *
     * @return string Категория получателя.
     */
    public function getCategory(): string
    {
        return $this->category;
    }
}
