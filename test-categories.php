<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo 'Connected database: ';
echo $pdo->query('SELECT DATABASE()')->fetchColumn();
echo PHP_EOL . PHP_EOL;

echo 'Category count: ';
echo $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
echo PHP_EOL . PHP_EOL;

$statement = $pdo->query(
    'SELECT id, name, slug FROM categories ORDER BY name ASC'
);

print_r($statement->fetchAll(PDO::FETCH_ASSOC));
