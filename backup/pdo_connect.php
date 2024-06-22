<?php
// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'web_database';
$user = 'postgres';
$pass = '1234';
$port = '5433';

$dsn = "pgsql:host=$host;dbname=$dbname;user=$user;password=$pass;port=$port";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    // Создаем объект PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Подключено к БД";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
