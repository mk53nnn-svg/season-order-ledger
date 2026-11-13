<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$pdo = get_pdo();
$stmt = $pdo->query("SELECT id, name, reading FROM clients ORDER BY COALESCE(NULLIF(reading, ''), name) COLLATE utf8mb4_ja_0900_as_cs ASC, id ASC");
echo json_encode(['ok' => true, 'clients' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);