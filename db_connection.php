<?php


$db_path = __DIR__ . '/storage/database.sqlite';


if (!file_exists(__DIR__ . '/storage')) {
    mkdir(__DIR__ . '/storage', 0777, true);
}

try {
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>