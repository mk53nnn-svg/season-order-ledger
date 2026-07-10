<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$seasons = get_all_seasons();
$activeSeason = get_active_season();
$selectedSeasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : ($activeSeason['id'] ?? 0);

if (!$selectedSeasonId) {
    echo 'シーズンが登録されていません。';
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="common.css">
<title>一括発注入力 - シーズン受注台帳</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", "Yu Gothic", sans-serif; background: #f5f5f5; color: #222; }
  .page { max-width: 1000px; margin: 0 auto; padding: 24px 16px; }
  .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px; }
  .header h1 { font-size: 18px; font-weight: 600; }
  .header .season { font-size: 13px; color: #666; background: #fff; padding: 4px 10px; border-radius: 999px; }
  .note { font-size: 12px; color: #999; margin-bottom: 16px; }
  .genre-group { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-bottom: 10px; }
  .genre-name { font-size: 14px; font-weight: 600; color: #222; padding: 12px 16px; border-bottom: 1px solid #eee; background: #fafafa; }
  table { width: 100%; border-collapse: collapse; font-size: 13px; }
  th { text-align: left; font-size: 11px; font-weight: 600; color: #888; padding: 8px 14px; border-bottom: 1px solid #eee; white-space: nowrap; }
  td { padding: 10px 14px; border-bottom: 1px solid #eee; vertical-align: middle; }
  tr:last-child td { border-bottom: none; }
  tr.urgent td { background: #fdecea; }
  .num { text-align: right; font-variant-numeric: tabular-nums; }
  .stock-minus { color: #c0392b; font-weight: 700; }
  .stock-plus { color: #222; }
  .product-name { font-weight: 500; color: #222; }
  .code-chip { font-size: 11px; color: #888; background: #f5f5f5; border: 1px solid #ddd; padding: 2px 8px; border-radius: 999px; white-space: nowrap; }
  .order-input { width: 80px; height: 32px; border-radius: 6px; border: 1px solid #ccc; padding: 0 8px; font-size: 13px; text-align: right; }
  .order-input:focus { outline: none; border-color: #4a90d9; }
  .actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
  .btn-primary { height: 44px; padding: 0 32px; border-radius: 8px; border: none; background: #222; color: #fff; font-size: 15px; font-weight: 600; cursor: pointer; }
  .btn-secondary { height: 44px; padding: 0 20px; border-radius: 8px; border: 1px solid #ccc; background: #fff; color: #555; font-size: 14px; cursor: pointer; }
  .msg { margin-top: 14px; font-size: 13px; padding: 10px 14px; border-radius: 8px; display: none; }
  .msg.success { background: #e6f4ea; color: #1e7e34; display: block; }
  .msg.error { background: #fdecea; color: #c0392b; display: block; }
  .date-row { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
  .date-row label { font-size: 13px; color: #555; }
  .date-row input { height: 36px; border-radius: 8px; border: 1px solid #ccc; padding: 0 10px; font-size: 14px; }
  .loading { padding: 40px; text-align: center; color: #999; }
  .code-chip.copied { background: #e6f4ea; border-color: #b7d9bf; color: #1e7e34; }
  .copy-toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(10px); background: #222; color: #fff; font-size: 12px; padding: 8px 16px; border-radius: 8px; opacity: 0; transition: all 0.2s; pointer-events: none; z-index: 100; }
  .copy-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
  .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; }
  .modal-overlay.show { display: flex; align-items: center; justify-content: center; }
  .modal { background: #fff; border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; }
  .modal h2 { font-size: 16px; font-weight: 600; margin-bottom: 6px; }
  .modal .sub { font-size: 12px; color: #888; margin-bottom: 16px; }
  .modal table { width: 100%; font-size: 13px; border-collapse: collapse; }
  .modal th { text-align: left; font-size: 11px; color: #888; padding: 6px 8px; border-bottom: 1px solid #eee; }
  .modal td { padding: 8px 8px; border-bottom: 1px solid #eee; }
  .modal-actions { display: flex; gap: 10px; margin-top: 16px; }
  .modal-actions .btn-primary { flex: 1; height: 40px; border-radius: 8px; border: none; background: #222; color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; }
  .modal-actions .btn-cancel { height: 40px; padding: 0 20px; border-radius: 8px; border: 1px solid #ccc; background: #fff; color: #555; font-size: 14px; cursor: pointer; }
</style>
</head>
<body>
<div style="padding:8px 16px;background:#fff;border-bottom:1px solid #eee;"><a href="index.php" style="font-size:12px;color:#888;text-decoration:none;">&laquo; ホーム</a></div>
<div class="page">
  <div class="header">
    <h1>一括発注入力</h1>
    <span class="season" id="season-name"><?= htmlspecialchars($activeSeason['name'] ?? '') ?></span>
  </div>

  <div class="date-row">
    <label>発注日：</label>
    <input type="date" id="order-date">
  </div>

  <p class="note">発注数を入力して「一括発注を登録する」ボタンを押してください。数量が空欄の商品はスキップされます。</p>

  <div id="container"><div class="loading">読み込み中...</div></div>

  <div class="actions">
    <button class="btn-secondary" onclick="window.history.back()">戻る</button>
    <button class="btn-primary" onclick="submitBulkOrder()">一括発注を登録する</button>
  </div>
  <div class="msg" id="msg"></div>
</div>

<div class="copy-toast" id="copy-toast">コードをコピーしました</div>

<!-- 確認モーダル -->
<div class="modal-overlay" id="confirm-modal">
  <div class="modal">
    <h2>発注内容の確認</h2>
    <p class="sub" id="confirm-date"></p>
    <table>
      <thead><tr><th>商品名</th><th style="text-align:right;">発注数</th></tr></thead>
      <tbody id="confirm-tbody"></tbody>
    </table>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeConfirm()">修正する</button>
      <button class="btn-primary" onclick="executeOrder()">登録する</button>
    </div>
  </div>
</div>

<script>
const SEASON_ID = <?= (int)$selectedSeasonId ?>;
let summaryData = [];

document.getElementById('order-date').value = new Date().toISOString().slice(0, 10);

async function loadData() {
  const res = await fetch(`../api/get_summary.php?season_id=${SEASON_ID}`);
  const result = await res.json();
  if (!result.ok) {
    document.getElementById('container').innerHTML = '<div class="loading">読み込みに失敗しました</div>';
    return;
  }
  summaryData = result.data;
  renderTable(summaryData);
}

function renderTable(data) {
  const groups = {};
  data.forEach(row => {
    if (!groups[row.genre_name]) groups[row.genre_name] = [];
    groups[row.genre_name].push(row);
  });

  document.getElementById('container').innerHTML = Object.entries(groups).map(([genreName, rows]) => {
    const tableRows = rows.map(row => {
      const isUrgent = row.stock < 0;
      const stockClass = row.stock < 0 ? 'stock-minus' : 'stock-plus';
      return `
        <tr class="${isUrgent ? 'urgent' : ''}">
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <span class="product-name">${escapeHtml(row.product_name)}</span>
              ${row.product_code ? `<span class="code-chip" onclick="copyCode('${row.product_code}', this)" style="cursor:pointer;" title="クリックでコピー">${escapeHtml(row.product_code)}</span>` : ''}
              ${row.unit_quantity ? `<span style="font-size:11px;color:#2b6cb0;">入数：${escapeHtml(row.unit_quantity)}</span>` : ''}
            </div>
          </td>
          <td class="num">${row.order_qty_sum}</td>
          <td class="num">${row.po_qty_sum}</td>
          <td class="num ${stockClass}">${row.stock}</td>
          <td class="num">
            <input type="number" class="order-input" min="0" placeholder="―"
              data-product-id="${row.product_id}">
          </td>
        </tr>`;
    }).join('');

    return `
      <div class="genre-group">
        <div class="genre-name">${escapeHtml(genreName)}</div>
        <table>
          <thead>
            <tr>
              <th>商品名 ／ コード</th>
              <th class="num" style="width:90px;">受注数</th>
              <th class="num" style="width:90px;">発注数</th>
              <th class="num" style="width:80px;">在庫</th>
              <th class="num" style="width:100px;">発注入力</th>
            </tr>
          </thead>
          <tbody>${tableRows}</tbody>
        </table>
      </div>`;
  }).join('');
}

let pendingItems = [];

function submitBulkOrder() {
  const msg = document.getElementById('msg');
  msg.className = 'msg';

  const orderDate = document.getElementById('order-date').value;
  if (!orderDate) {
    msg.className = 'msg error';
    msg.textContent = '発注日を入力してください。';
    return;
  }

  pendingItems = [];
  document.querySelectorAll('.order-input').forEach(input => {
    const qty = parseInt(input.value);
    if (qty > 0) {
      const row = input.closest('tr');
      const productName = row.querySelector('.product-name').textContent;
      pendingItems.push({
        product_id: parseInt(input.dataset.productId),
        product_name: productName,
        quantity: qty,
        order_date: orderDate,
      });
    }
  });

  if (pendingItems.length === 0) {
    msg.className = 'msg error';
    msg.textContent = '発注数を1件以上入力してください。';
    return;
  }

  // 確認モーダルを表示
  const d = new Date(orderDate);
  document.getElementById('confirm-date').textContent =
    `発注日：${d.getFullYear()}年${d.getMonth()+1}月${d.getDate()}日　合計 ${pendingItems.length} 件`;
  document.getElementById('confirm-tbody').innerHTML = pendingItems.map(item => `
    <tr>
      <td>${escapeHtml(item.product_name)}</td>
      <td style="text-align:right;">${item.quantity}</td>
    </tr>`).join('');
  document.getElementById('confirm-modal').classList.add('show');
}

function closeConfirm() {
  document.getElementById('confirm-modal').classList.remove('show');
}

async function executeOrder() {
  closeConfirm();
  const msg = document.getElementById('msg');
  msg.className = 'msg';

  try {
    const res = await fetch('../api/bulk_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ season_id: SEASON_ID, items: pendingItems }),
    });
    const result = await res.json();
    if (result.ok) {
      msg.className = 'msg success';
      msg.textContent = `${pendingItems.length}件の発注を登録しました。`;
      document.querySelectorAll('.order-input').forEach(i => i.value = '');
      await loadData();
    } else {
      msg.className = 'msg error';
      msg.textContent = '登録に失敗しました：' + (result.error || '不明なエラー');
    }
  } catch (e) {
    msg.className = 'msg error';
    msg.textContent = '通信エラーが発生しました。';
  }
}

function copyCode(code, el) {
  navigator.clipboard.writeText(code).catch(() => {});
  el.classList.add('copied');
  const toast = document.getElementById('copy-toast');
  toast.classList.add('show');
  setTimeout(() => el.classList.remove('copied'), 1000);
  setTimeout(() => toast.classList.remove('show'), 1400);
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

loadData();
</script>
</body>
</html>
