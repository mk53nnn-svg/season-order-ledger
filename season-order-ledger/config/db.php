<?php
/**
 * DB接続設定
 * さくらインターネットのサーバー情報に合わせて編集してください
 */
declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'season_order_ledger';
const DB_USER = 'your_db_user';      // ← 実際のDBユーザー名に変更
const DB_PASS = 'your_db_password';  // ← 実際のDBパスワードに変更

function get_pdo(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
