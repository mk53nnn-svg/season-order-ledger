<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/**
 * 現在アクティブなシーズンを取得する
 */
function get_active_season(): ?array
{
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT * FROM seasons WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $season = $stmt->fetch();
    return $season ?: null;
}

/**
 * シーズン一覧を新しい順で取得する（プルダウン表示用）
 */
function get_all_seasons(): array
{
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT * FROM seasons ORDER BY start_date DESC");
    return $stmt->fetchAll();
}

/**
 * ジャンル一覧を表示順で取得する
 */
function get_genres(): array
{
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT * FROM genres WHERE is_active = 1 ORDER BY display_order ASC, id ASC");
    return $stmt->fetchAll();
}

/**
 * 指定ジャンルに属する商品一覧を表示順で取得する
 */
function get_products_by_genre(int $genreId): array
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare(
        "SELECT * FROM products WHERE genre_id = :genre_id AND is_active = 1 ORDER BY display_order ASC, id ASC"
    );
    $stmt->execute(['genre_id' => $genreId]);
    return $stmt->fetchAll();
}

/**
 * 納期表示用の文字列を整形する
 * delivery_type が 'date' の場合は日付を、それ以外はそのままラベルを返す
 */
function format_delivery(?string $deliveryType, ?string $deliveryDate): string
{
    if ($deliveryType === 'date' && $deliveryDate) {
        $d = new DateTime($deliveryDate);
        return $d->format('n/j');
    }
    return $deliveryType;
}
