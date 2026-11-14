<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
use App\Core\EntityFactory;
use App\Models\Ticket;
use App\Services\ConsoleLogger;
use App\Services\EntityService;
use App\Services\FileLogger;
use App\Tasks\CompanyDirector;
use App\Tasks\DepartmentHead;
use App\Tasks\Product as TaskProduct;
use App\Tasks\ShopManager;
use App\Tasks\Store;
use App\Tasks\Triangle;
use App\Tasks\Worker;
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Лабораторна робота №4: ООП та патерни проєктування</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        h1 { color: #2c3e50; }
        h2 { color: #34495e; border-bottom: 2px solid #ecf0f1; padding-bottom: 5px; }
        pre { background: #f8f9fa; padding: 12px; border-left: 4px solid #3498db; }
        hr { border: 0; border-top: 1px solid #bdc3c7; margin: 20px 0; }
    </style>
</head>
<body>

<h1>Лабораторна робота №4: ООП в PHP та патерни проєктування</h1>
<hr>

<h2>1. Патерн Singleton (Підключення до БД)</h2>
<?php
$db1 = Database::getInstance();
$db2 = Database::getInstance();
echo 'Перевірка єдиного екземпляра ($db1 === $db2): <strong>' . ($db1 === $db2 ? 'true' : 'false') . '</strong><br>';
?>
<hr>

<h2>2. Патерн Factory, Спадкування та Трейт експорту в JSON</h2>
<?php
$ticket = EntityFactory::createEntity('ticket', [
    'id' => '1042',
    'createdAt' => '2026-11-14 12:00:00',
    'passengerFullName' => 'Катерина Сенів',
    'carNumber' => 5,
    'seatNumber' => 12,
    'price' => 450.50,
]);

$passenger = EntityFactory::createEntity('passenger', [
    'id' => '77',
    'createdAt' => '2026-10-01 09:30:00',
    'name' => 'Катерина Сенів',
    'email' => 'kateryna.seniv@example.com',
    'phone' => '+380671234567',
]);

echo '<strong>Деталі квитка:</strong> ' . $ticket->getDetails() . '<br>';
echo '<strong>Деталі пасажира:</strong> ' . $passenger->getDetails() . '<br>';

if ($ticket instanceof Ticket) {
    echo '<strong>Експорт квитка у JSON (JsonExportTrait):</strong>';
    echo '<pre>' . htmlspecialchars($ticket->exportToJson()) . '</pre>';
}
?>
<hr>

<h2>3. Dependency Injection (EntityService + LoggerInterface)</h2>
<?php
$consoleLogger = new ConsoleLogger();
$serviceWithConsole = new EntityService($consoleLogger);
$serviceWithConsole->registerEntity($ticket);
$serviceWithConsole->calculateTicketTotal(450.50, 2);

echo '<br>';

$fileLogger = new FileLogger();
$serviceWithFile = new EntityService($fileLogger);
$serviceWithFile->registerEntity($passenger);
?>
<hr>

<h2>4. Індивідуальне архітектурне завдання (Варіант 10)</h2>
<?php
$store = new Store(1, 'Головний склад залізничного спорядження');
$store->addProduct(new TaskProduct(101, 'Комплект постільної білизни', 80.0));
$store->addProduct(new TaskProduct(102, 'Чай залізничний у підскляннику', 25.0));
$store->addProduct(new TaskProduct(103, 'Комплект рушників', 60.0));

$found = $store->searchProduct('Комплект');
echo '<strong>Композиція (Store/Product):</strong> Знайдено товарів за запитом "Комплект": ' . count($found) . '<br>';
$store->removeProduct(102);

echo '<br><strong>Ієрархія працівників (calculateSalary):</strong><br>';
$employees = [
    new Worker('Іван Петренко (Робітник)', 150.0, 160),
    new ShopManager('Олена Коваль (Керівник цеху)', 150.0, 160, 5000.0),
    new DepartmentHead('Сергій Бондар (Начальник відділу)', 150.0, 160, 5000.0, 8000.0),
    new CompanyDirector('Катерина Сенів (Керівник підприємства)', 150.0, 160, 5000.0, 8000.0, 1.5),
];

foreach ($employees as $emp) {
    echo "&bull; {$emp->getName()} — Зарплата: <strong>{$emp->calculateSalary()} грн</strong><br>";
}

$triangle = new Triangle(3.0, 4.0, 5.0);
echo "<br><strong>Трикутник (3, 4, 5):</strong> Периметр = <strong>{$triangle->getPerimeter()}</strong>, Площа = <strong>{$triangle->getArea()}</strong><br>";
?>

</body>
</html>