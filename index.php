<?php

declare(strict_types=1);

// Классы подключаются до session_start(), чтобы PHP смог восстановить объекты из сессии
require_once __DIR__ . '/src/Merchant.php';
require_once __DIR__ . '/src/Transaction.php';
require_once __DIR__ . '/src/TransactionStorageInterface.php';
require_once __DIR__ . '/src/TransactionRepository.php';
require_once __DIR__ . '/src/TransactionManager.php';
require_once __DIR__ . '/src/TransactionTableRenderer.php';

session_start();

/**
 * Создаёт репозиторий с начальными данными (10 транзакций).
 *
 * @return TransactionRepository Заполненный репозиторий.
 */
function createInitialRepository(): TransactionRepository
{
    // Получатели платежей
    $superMart = new Merchant('SuperMart', 'Продукты');
    $techStore = new Merchant('TechStore', 'Электроника');
    $netProvider = new Merchant('NetProvider', 'Связь');
    $taxiGo = new Merchant('TaxiGo', 'Транспорт');
    $pizzaHouse = new Merchant('Pizza House', 'Рестораны');
    $cinemaCity = new Merchant('Cinema City', 'Развлечения');
    $pharmaPlus = new Merchant('PharmaPlus', 'Здоровье');
    $fitLife = new Merchant('FitLife Gym', 'Спорт');

    $transactions = [
        new Transaction(1, new DateTime('2026-05-09'), 152.40, 'Покупка продуктов на неделю', $superMart),
        new Transaction(2, new DateTime('2026-01-15'), 1299.99, 'Новый ноутбук', $techStore),
        new Transaction(3, new DateTime('2026-03-01'), 25.00, 'Оплата интернета за март', $netProvider),
        new Transaction(4, new DateTime('2026-10-05'), 12.75, 'Поездка в аэропорт', $taxiGo),
        new Transaction(5, new DateTime('2026-02-03'), 48.60, 'Ужин с друзьями', $pizzaHouse),
        new Transaction(6, new DateTime('2026-08-27'), 18.00, 'Билеты в кино', $cinemaCity),
        new Transaction(7, new DateTime('2026-03-18'), 34.90, 'Лекарства и витамины', $pharmaPlus),
        new Transaction(8, new DateTime('2026-07-01'), 60.00, 'Абонемент в спортзал', $fitLife),
        new Transaction(9, new DateTime('2026-06-14'), 89.15, 'Продукты к празднику', $superMart),
        new Transaction(10, new DateTime('2026-04-22'), 249.50, 'Беспроводные наушники', $techStore),
    ];

    $repository = new TransactionRepository();
    foreach ($transactions as $transaction) {
        $repository->addTransaction($transaction);
    }

    return $repository;
}

/**
 * Возвращает следующий свободный id (максимальный id + 1).
 *
 * @param TransactionStorageInterface $repository Хранилище транзакций.
 * @return int Новый id.
 */
function getNextId(TransactionStorageInterface $repository): int
{
    $maxId = 0;

    foreach ($repository->getAllTransactions() as $transaction) {
        $maxId = max($maxId, $transaction->getId());
    }

    return $maxId + 1;
}

/**
 * Создаёт транзакцию из данных формы добавления.
 *
 * @param array<string, mixed> $data Данные формы ($_POST).
 * @param int                  $id   Идентификатор новой транзакции.
 * @return Transaction Новая транзакция.
 * @throws InvalidArgumentException Если данные формы некорректны.
 */
function createTransactionFromForm(array $data, int $id): Transaction
{
    $dateString = trim((string)($data['date'] ?? ''));
    $amountString = trim((string)($data['amount'] ?? ''));
    $description = trim((string)($data['description'] ?? ''));
    $merchantName = trim((string)($data['merchant'] ?? ''));
    $category = trim((string)($data['category'] ?? ''));

    $date = DateTime::createFromFormat('!Y-m-d', $dateString);
    if ($date === false || $date->format('Y-m-d') !== $dateString) {
        throw new InvalidArgumentException('Укажите корректную дату.');
    }
    if ($date > new DateTime()) {
        throw new InvalidArgumentException('Дата транзакции не может быть в будущем.');
    }
    if (!is_numeric($amountString) || (float)$amountString <= 0) {
        throw new InvalidArgumentException('Сумма должна быть положительным числом.');
    }
    if ($description === '' || $merchantName === '' || $category === '') {
        throw new InvalidArgumentException('Заполните описание, получателя и категорию.');
    }

    return new Transaction($id, $date, (float)$amountString, $description, new Merchant($merchantName, $category));
}

// Репозиторий хранится в сессии, чтобы изменения сохранялись между запросами
if (!isset($_SESSION['repository']) || !($_SESSION['repository'] instanceof TransactionRepository)) {
    $_SESSION['repository'] = createInitialRepository();
}

/** @var TransactionRepository $repository */
$repository = $_SESSION['repository'];

// Обработка форм добавления, удаления и сброса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'add') {
            $transaction = createTransactionFromForm($_POST, getNextId($repository));
            $repository->addTransaction($transaction);
            $_SESSION['message'] = ['success', "Транзакция с id {$transaction->getId()} добавлена."];
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($repository->findById($id) === null) {
                throw new InvalidArgumentException("Транзакция с id {$id} не найдена.");
            }
            $repository->removeTransactionById($id);
            $_SESSION['message'] = ['success', "Транзакция с id {$id} удалена."];
        } elseif ($action === 'reset') {
            $_SESSION['repository'] = createInitialRepository();
            $_SESSION['message'] = ['success', 'Восстановлены начальные данные.'];
        }
    } catch (InvalidArgumentException $e) {
        $_SESSION['message'] = ['error', $e->getMessage()];
    }

    // Перенаправление, чтобы при обновлении страницы форма не отправлялась повторно
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// Сообщение о результате последнего действия (показывается один раз)
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

// Поиск по id
$searchId = isset($_GET['search_id']) && $_GET['search_id'] !== '' ? (int)$_GET['search_id'] : null;
$found = $searchId !== null ? $repository->findById($searchId) : null;

$manager = new TransactionManager($repository);
$renderer = new TransactionTableRenderer();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Банковские транзакции</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        table { border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #999; padding: 6px 12px; text-align: left; }
        th { background: #eee; }
        form { margin-bottom: 20px; }
        input { margin: 0 6px 6px 0; padding: 4px; }
        .forms { display: flex; gap: 40px; flex-wrap: wrap; }
        .message { padding: 8px 12px; margin-bottom: 20px; }
        .success { background: #dff0d8; }
        .error { background: #f2dede; }
    </style>
</head>
<body>
    <?php if ($message !== null): ?>
        <div class="message <?php echo $message[0]; ?>"><?php echo htmlspecialchars($message[1], ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="forms">
        <form method="post">
            <h3>Добавить транзакцию</h3>
            <input type="hidden" name="action" value="add">
            <input type="date" name="date" max="<?php echo date('Y-m-d'); ?>" required><br>
            <input type="number" name="amount" step="0.01" min="0.01" placeholder="Сумма" required><br>
            <input type="text" name="description" placeholder="Описание" required><br>
            <input type="text" name="merchant" placeholder="Получатель" required><br>
            <input type="text" name="category" placeholder="Категория" required><br>
            <button type="submit">Добавить</button>
        </form>

        <form method="post">
            <h3>Удалить транзакцию</h3>
            <input type="hidden" name="action" value="delete">
            <input type="number" name="id" min="1" placeholder="ID" required>
            <button type="submit">Удалить</button>
        </form>

        <form method="get">
            <h3>Найти транзакцию</h3>
            <input type="number" name="search_id" min="1" placeholder="ID" value="<?php echo $searchId ?? ''; ?>" required>
            <button type="submit">Найти</button>
        </form>

        <form method="post">
            <h3>Сброс</h3>
            <input type="hidden" name="action" value="reset">
            <button type="submit">Вернуть начальные данные</button>
        </form>
    </div>

    <?php if ($searchId !== null): ?>
        <h2>Результат поиска (id = <?php echo $searchId; ?>)</h2>
        <?php if ($found !== null): ?>
            <?php echo $renderer->render([$found]); ?>
        <?php else: ?>
            <p>Транзакция не найдена.</p>
        <?php endif; ?>
    <?php endif; ?>

    <h2>Все транзакции</h2>
    <?php echo $renderer->render($repository->getAllTransactions()); ?>

    <h2>Сортировка по дате</h2>
    <?php echo $renderer->render($manager->sortTransactionsByDate()); ?>

    <h2>Сортировка по сумме (по убыванию)</h2>
    <?php echo $renderer->render($manager->sortTransactionsByAmountDesc()); ?>

    <h2>Вычисления</h2>
    <ul>
        <li>Общая сумма: <?php echo number_format($manager->calculateTotalAmount(), 2, '.', ' '); ?></li>
        <li>Сумма за 01.03.2026 – 30.06.2026: <?php echo number_format($manager->calculateTotalAmountByDateRange('2026-03-01', '2026-06-30'), 2, '.', ' '); ?></li>
        <li>Транзакций у SuperMart: <?php echo $manager->countTransactionsByMerchant('SuperMart'); ?></li>
    </ul>
</body>
</html>
