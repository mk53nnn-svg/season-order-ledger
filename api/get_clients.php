<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$pdo = get_pdo();
$stmt = $pdo->query("SELECT id, name FROM clients ORDER BY display_order ASC, id ASC");
echo json_encode(['ok' => true, 'clients' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);