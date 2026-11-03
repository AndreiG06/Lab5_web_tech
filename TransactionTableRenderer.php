<?php

declare(strict_types=1);

/**
 * Формирует HTML-таблицу из списка транзакций.
 *
 * Отвечает только за представление данных и не содержит бизнес-логики.
 */
final class TransactionTableRenderer
{
    /**
     * Возвращает HTML-код таблицы с транзакциями.
     *
     * @param Transaction[] $transactions Массив транзакций для вывода.
     * @return string HTML-код таблицы.
     */
    public function render(array $transactions): string
    {
        $html = '<table>';
        $html .= '<thead><tr>'
            . '<th>ID</th>'
            . '<th>Дата</th>'
            . '<th>Сумма</th>'
            . '<th>Описание</th>'
            . '<th>Получатель</th>'
            . '<th>Категория</th>'
            . '<th>Дней прошло</th>'
            . '</tr></thead>';
        $html .= '<tbody>';

        foreach ($transactions as $transaction) {
            $html .= $this->renderRow($transaction);
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Формирует одну строку таблицы.
     *
     * @param Transaction $transaction Транзакция.
     * @return string HTML-код строки.
     */
    private function renderRow(Transaction $transaction): string
    {
        $merchant = $transaction->getMerchant();

        return '<tr>'
            . '<td>' . $transaction->getId() . '</td>'
            . '<td>' . $transaction->getDate()->format('d.m.Y') . '</td>'
            . '<td>' . number_format($transaction->getAmount(), 2, '.', ' ') . '</td>'
            . '<td>' . $this->escape($transaction->getDescription()) . '</td>'
            . '<td>' . $this->escape($merchant->getName()) . '</td>'
            . '<td>' . $this->escape($merchant->getCategory()) . '</td>'
            . '<td>' . $transaction->getDaysSinceTransaction() . '</td>'
            . '</tr>';
    }

    /**
     * Экранирует строку для безопасного вывода в HTML.
     *
     * @param string $value Исходная строка.
     * @return string Экранированная строка.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
