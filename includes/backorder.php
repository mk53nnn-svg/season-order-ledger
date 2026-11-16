<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Tokyo');

// 出荷予定日から平日2日後（土日を除く）の日付を返す
function backorder_release_date(string $expectedDate): string
{
    $d = new DateTime($expectedDate);
    $added = 0;
    while ($added < 2) {
        $d->modify('+1 day');
        if ((int)$d->format('N') < 6) {
            $added++;
        }
    }
    return $d->format('Y-m-d');
}

// 品切れ中として扱う期間内かどうか
function is_backorder_active(?string $expectedDate): bool
{
    if (!$expectedDate) {
        return false;
    }
    $today = (new DateTime('today'))->format('Y-m-d');
    return $today < backorder_release_date($expectedDate);
}