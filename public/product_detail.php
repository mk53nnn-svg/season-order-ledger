<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$seasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;

if ($productId <= 0 || $seasonId <= 0) {
    echo 'パラメータが不足しています。';
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="common.css">
<title>商品詳細 - シーズン受注台帳</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", "Yu Gothic", sans-serif; background: #f5f5f5; color: #222; }
  .page { max-width: 760px; margin: 0 auto; padding: 24px 16px; }
  .breadcrumb { font-size: 12px; color: #888; margin-bottom: 12px; cursor: pointer; }
  .breadcrumb:hover { color: #2b6cb0; }
  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
  .header h1 { font-size: 19px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
  .code { font-size: 12px; color: #888; font-weight: 400; }
  .genre-tag { font-size: 11px; color: #888; background: #f0f0f0; padding: 3px 9px; border-radius: 999px; }
  .stat-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px; }
  .card { background: #fff; border-radius: 10px; padding: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
  .card.clickable { cursor: pointer; }
  .card .label { font-size: 11px; color: #888; margin-bottom: 4px; }
  .card .value { font-size: 22px; font-weight: 700; }
  .card.danger .value { color: #c0392b; }
  .card.success .value { color: #1e7e34; }
  .po-history { display: none; background: #fff; border-radius: 10px; padding: 12px 14px; margin-bottom: 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
  .po-history.show { display: block; }
  .po-history table { width: 100%; font-size: 13px; }
  .po-history th { text-align: left; font-size: 11px; color: #888; padding: 4px 8px; }
  .po-history td { padding: 6px 8px; border-top: 1px solid #f0f0f0; vertical-align: middle; }
  .stock-panel { background: #fff; border-radius: 10px; padding: 14px 16px; margin-bottom: 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
  .stock-panel-title { font-size: 13px; font-weight: 600; color: #555; margin-bottom: 8px; }
  .stock-row { display: flex; gap: 10px; align-items: center; }
  .stock-row input { width: 100px; height: 34px; border-radius: 6px; border: 1px solid #ccc; padding: 0 8px; font-size: 14px; }
  .btn-mini-save { height: 34px; padding: 0 14px; border-radius: 6px; border: none; background: #222; color: #fff; font-size: 12px; cursor: pointer; }
  .order-panel { background: #fff; border-radius: 10px; padding: 14px 16px; margin-bottom: 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
  .order-panel-title { font-size: 13px; font-weight: 600; color: #555; margin-bottom: 10px; }
  .order-row { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
  .field { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 130px; }
  .field label { font-size: 12px; color: #555; }
  .field input { height: 36px; border-radius: 6px; border: 1px solid #ccc; padding: 0 10px; font-size: 14px; }
  .btn-save { height: 36px; padding: 0 18px; border-radius: 6px; border: none; background: #222; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }
  .saved-msg { font-size: 12px; color: #1e7e34; margin-top: 8px; display: none; }
  .saved-msg.show { display: block; }
  .section-title { font-size: 13px; font-weight: 600; color: #555; margin-bottom: 8px; margin-top: 18px; }
  table.main-table { width: 100%; border-collapse: collapse; font-size: 13px; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
  table.main-table th { text-align: left; font-size: 11px; font-weight: 600; color: #888; padding: 8px 12px; border-bottom: 1px solid #eee; }
  table.main-table td { padding: 9px 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
  .num { text-align: right; }
  .edit-btn { font-size: 11px; color: #2b6cb0; border: 1px solid #2b6cb0; background: #eef5fc; border-radius: 6px; padding: 3px 9px; cursor: pointer; }
  .delete-btn { font-size: 11px; color: #c0392b; border: 1px solid #c0392b; background: #fdecea; border-radius: 6px; padding: 3px 9px; cursor: pointer; }
  .total-row td { font-weight: 700; background: #fafafa; }
  .edit-row input, .edit-row select { height: 30px; border-radius: 6px; border: 1px solid #ccc; padding: 0 6px; font-size: 12px; width: 100%; }
  .edit-actions { display: flex; gap: 6px; }
  .btn-mini { font-size: 11px; padding: 3px 8px; border-radius: 6px; border: none; cursor: pointer; }
  .btn-mini-confirm { background: #222; color: #fff; }
  .btn-mini-cancel { background: #fff; border: 1px solid #ccc; color: #888; }
  .loading { padding: 30px; text-align: center; color: #999; }
  .row-actions { display: flex; gap: 6px; }

  @media (max-width: 600px) {
    .page { padding: 12px 8px; }
    .header { flex-direction: column; gap: 6px; }
    .header h1 { font-size: 16px; }
    .stat-cards { grid-template-columns: repeat(3, 1fr); gap: 6px; }
    .card { padding: 10px 8px; }
    .card .value { font-size: 18px; }
    .card .label { font-size: 10px; }
    .order-panel { padding: 12px; }
    .order-row { flex-direction: column; gap: 8px; }
    .field input { font-size: 16px; }
    .btn-save { width: 100%; height: 44px; font-size: 15px; }
    .po-history table { font-size: 12px; }
    .section-title { font-size: 14px; margin-top: 14px; }
    table.main-table { font-size: 12px; }
    table.main-table th { padding: 6px 8px; font-size: 10px; }
    table.main-table td { padding: 8px 8px; }
    .edit-btn { font-size: 11px; padding: 3px 7px; }
    .delete-btn { font-size: 11px; padding: 3px 7px; }
    .breadcrumb { font-size: 13px; margin-bottom: 8px; }
    .stock-panel { padding: 12px; }
    .stock-row { flex-wrap: wrap; }
    .order-panel { display: none; }
    .stock-panel { display: none; }
    .home-link { display: none; }
  }
</style>
</head>
<body>
<div class="home-link" style="padding:8px 16px;background:#fff;border-bottom:1px solid #eee;"><a href="index.php" style="font-size:12px;color:#888;text-decoration:none;">&laquo; ホーム</a></div>
<div class="page" id="page-root">
  <div class="breadcrumb" onclick="goBack()">← 商品別受注集計に戻る</div>
  <div class="loading" id="loading">読み込み中...</div>
  <div id="content" style="display:none;"></div>
</div>

<script>
const PRODUCT_ID = <?= (int)$productId ?>;
const SEASON_ID = <?= (int)$seasonId ?>;

let currentData = null;

function goBack() {
  window.location.href = `summary.php?season_id=${SEASON_ID}`;
}

async function loadStaff() {
  const res = await fetch('../api/staff.php');
  const result = await res.json();
  if (!result.ok) return;
  const select = document.getElementById('po-staff-select');
  if (!select) return;
  const defaultStaff = result.staff.find(s => s.is_default == 1);
  select.innerHTML = '<option value="">-- 選択 --</option>' +
    result.staff.map(s => `<option value="${escapeHtml(s.name)}">${escapeHtml(s.name)}</option>`).join('');
  if (defaultStaff) select.value = defaultStaff.name;
  select.addEventListener('change', () => {
    const input = document.getElementById('po-staff-input');
    if (input) { input.value = ''; input.disabled = !!select.value; input.style.background = select.value ? '#f0f0f0' : ''; }
  });
}

async function loadDetail() {
  const res = await fetch(`../api/get_product_detail.php?product_id=${PRODUCT_ID}&season_id=${SEASON_ID}`);
  const result = await res.json();
  if (!result.ok) {
    document.getElementById('loading').textContent = '読み込みに失敗しました：' + (result.error || '');
    return;
  }
  currentData = result;
  render();
  return true;
}

function render() {
  const d = currentData;
  document.getElementById('loading').style.display = 'none';
  const content = document.getElementById('content');
  content.style.display = 'block';

  const stockClass = d.stock < 0 ? 'danger' : 'success';

  const poRows = d.purchase_orders.length === 0
    ? '<tr><td colspan="4" style="color:#999;">発注履歴はまだありません</td></tr>'
    : d.purchase_orders.map(po => `
        <tr data-po-id="${po.id}" ${po.is_tanoroshi ? 'style="background:#f0f5ff;"' : ''}>
          <td class="po-date">${po.is_tanoroshi ? '<span style="font-size:11px;color:#2b6cb0;font-weight:600;">棚卸在庫</span>' : po.order_date}</td>
          <td class="num po-qty">${po.quantity}</td>
          <td class="po-staff">${escapeHtml(po.staff_name || '')}</td>
          <td colspan="2">
            <div class="row-actions">
              <button class="edit-btn" onclick="startEditPo(this, ${po.id}, ${po.is_tanoroshi})">編集</button>
              <button class="delete-btn" onclick="deletePo(${po.id})">削除</button>
            </div>
          </td>
        </tr>`).join('') +
      `<tr class="total-row"><td style="text-align:right;color:#888;font-size:12px;">合計</td><td class="num">${d.po_total}</td><td colspan="2"></td></tr>`;

  content.innerHTML = `
    <div class="header">
      <h1>${escapeHtml(d.product.product_name)} <span class="code">${escapeHtml(d.product.product_code)}</span>${d.product.unit_quantity ? `<span style="font-size:12px;color:#2b6cb0;font-weight:400;">入数：${escapeHtml(d.product.unit_quantity)}</span>` : ''}</h1>
      <span class="genre-tag">${escapeHtml(d.product.genre_name)}</span>
    </div>

    <div class="stat-cards">
      <div class="card"><div class="label">受注合計</div><div class="value">${d.order_total}</div></div>
      <div class="card clickable" onclick="togglePoHistory()">
        <div class="label">発注済（クリックで履歴）</div><div class="value">${d.po_total}</div>
      </div>
      <div class="card ${stockClass}"><div class="label">在庫（開始時+発注-受注）</div><div class="value">${d.stock}</div></div>
    </div>

    <div class="po-history" id="po-history">
      <table>
        <thead><tr><th>発注日</th><th class="num">発注数</th><th>発注者</th><th colspan="2"></th></tr></thead>
        <tbody id="po-tbody">${poRows}</tbody>
      </table>
    </div>

    <div class="order-panel">
      <div class="order-panel-title">発注入力</div>
      <div class="order-row">
        <div class="field">
          <label>発注数</label>
          <input type="number" id="po-qty-input" min="1" placeholder="例：15">
        </div>
        <div class="field">
          <label>発注日</label>
          <input type="date" id="po-date-input" value="${new Date().toISOString().slice(0,10)}">
        </div>
        <div class="field">
          <label>発注者（プルダウンまたは自由入力）</label>
          <select id="po-staff-select" style="width:100%;height:36px;border-radius:6px;border:1px solid #ccc;padding:0 8px;font-size:14px;margin-bottom:6px;"></select>
          <input type="text" id="po-staff-input" placeholder="または自由入力" style="width:100%;height:36px;border-radius:6px;border:1px solid #ccc;padding:0 8px;font-size:14px;">
        </div>
      </div>
      <button class="btn-save" onclick="savePurchaseOrder()" style="margin-top:10px;width:100%;">保存する</button>
      <div class="saved-msg" id="po-saved-msg">保存しました。発注履歴に追加されました</div>
    </div>

    <div class="section-title">受注内訳（${d.orders.length}件）</div>
    <table class="main-table">
      <thead>
        <tr><th>受注日</th><th>取引先</th><th>納期</th><th class="num">受注数</th><th></th></tr>
      </thead>
      <tbody id="order-tbody">
        ${d.orders.map(o => renderOrderRow(o)).join('')}
        <tr class="total-row">
          <td colspan="3" style="text-align:right;color:#888;font-size:12px;">合計</td>
          <td class="num" id="total-qty">${d.order_total}</td>
          <td></td>
        </tr>
      </tbody>
    </table>
  `;
}

function renderOrderRow(o) {
  const deliveryLabel = o.delivery_type === 'date'
    ? (o.delivery_date ? o.delivery_date : '−')
    : (o.delivery_type || '−');
  return `
    <tr data-id="${o.id}">
      <td class="td-date">${o.order_date}</td>
      <td class="td-client">${escapeHtml(o.client_name)}</td>
      <td class="td-deadline" data-type="${o.delivery_type || ''}" data-date="${o.delivery_date || ''}">${deliveryLabel}</td>
      <td class="num td-qty">${o.quantity}</td>
      <td><div class="row-actions">
        <button class="edit-btn" onclick="startEdit(this)">編集</button>
        <button class="delete-btn" onclick="deleteOrder(${o.id})">削除</button>
      </div></td>
    </tr>
  `;
}

function togglePoHistory() {
  document.getElementById('po-history').classList.toggle('show');
}



async function savePurchaseOrder() {
  const qty = parseInt(document.getElementById('po-qty-input').value) || 0;
  const date = document.getElementById('po-date-input').value;
  const staffSelect = document.getElementById('po-staff-select');
  const staffInput = document.getElementById('po-staff-input');
  const staffName = staffSelect && staffSelect.value ? staffSelect.value : (staffInput ? staffInput.value.trim() : '');
  if (qty <= 0 || !date) return;

  // 自由入力した場合はマスタに追加
  if (staffInput && staffInput.value.trim() && !staffSelect.value) {
    await fetch('../api/staff.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'add_from_input', name: staffInput.value.trim() }),
    });
    await loadStaff();
  }

  await fetch('../api/add_purchase_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ season_id: SEASON_ID, product_id: PRODUCT_ID, order_date: date, quantity: qty, staff_name: staffName }),
  });
  document.getElementById('po-saved-msg').classList.add('show');
  document.getElementById('po-qty-input').value = '';
  await loadDetail();
}

function startEditPo(btn, poId, isTanoroshi) {
  const row = btn.closest('tr');
  const date = isTanoroshi ? '' : row.querySelector('.po-date').textContent;
  const qty = row.querySelector('.po-qty').textContent;
  const staff = row.querySelector('.po-staff') ? row.querySelector('.po-staff').textContent : '';
  row.classList.add('edit-row');
  row.querySelector('.po-date').innerHTML = isTanoroshi
    ? '<span style="font-size:11px;color:#2b6cb0;font-weight:600;">棚卸在庫</span>'
    : `<input type="date" class="e-po-date" value="${date}">`;
  if (row.querySelector('.po-staff')) {
    row.querySelector('.po-staff').innerHTML = `<input type="text" class="e-po-staff" value="${escapeHtml(staff)}" style="width:80px;height:28px;border-radius:6px;border:1px solid #ccc;padding:0 6px;font-size:12px;">`;
  }
  row.querySelector('.po-qty').innerHTML = `<input type="number" class="e-po-qty" value="${qty}" style="text-align:right;">`;
  row.cells[2].colSpan = 1;
  row.cells[2].innerHTML = `<div class="edit-actions">
    <button class="btn-mini btn-mini-confirm" onclick="confirmEditPo(this, ${poId})">保存</button>
    <button class="btn-mini btn-mini-cancel" onclick="render()">取消</button>
  </div>`;
}

async function confirmEditPo(btn, poId) {
  const row = btn.closest('tr');
  const date = row.querySelector('.e-po-date') ? row.querySelector('.e-po-date').value : '棚卸';
  const qty = parseInt(row.querySelector('.e-po-qty').value) || 0;
  const staff = row.querySelector('.e-po-staff') ? row.querySelector('.e-po-staff').value.trim() : '';
  if (qty <= 0) return;
  await fetch('../api/update_purchase_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'update_purchase_order', id: poId, order_date: date, quantity: qty, staff_name: staff }),
  });
  await loadDetail();
  document.getElementById('po-history').classList.add('show');
}

async function deletePo(poId) {
  if (!confirm('この発注履歴を削除しますか？')) return;
  if (!confirm('本当に削除しますか？この操作は取り消せません。')) return;
  await fetch('../api/update_purchase_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete_purchase_order', id: poId }),
  });
  await loadDetail();
  document.getElementById('po-history').classList.add('show');
}

function startEdit(btn) {
  const row = btn.closest('tr');
  const date = row.querySelector('.td-date').textContent;
  const client = row.querySelector('.td-client').textContent;
  const deadlineCell = row.querySelector('.td-deadline');
  const deliveryType = deadlineCell.dataset.type;
  const deliveryDate = deadlineCell.dataset.date;
  const qty = row.querySelector('.td-qty').textContent;

  row.classList.add('edit-row');
  row.querySelector('.td-date').innerHTML = `<input type="date" class="e-date" value="${date}">`;
  row.querySelector('.td-client').innerHTML = `<input type="text" class="e-client" value="${client}">`;
  row.querySelector('.td-deadline').innerHTML = `
    <select class="e-delivery-type">
      <option value="">納期なし</option>
      <option value="date" ${deliveryType==='date'?'selected':''}>日付指定</option>
      <option value="即納" ${deliveryType==='即納'?'selected':''}>即納</option>
      <option value="初旬" ${deliveryType==='初旬'?'selected':''}>初旬</option>
      <option value="中旬" ${deliveryType==='中旬'?'selected':''}>中旬</option>
      <option value="下旬" ${deliveryType==='下旬'?'selected':''}>下旬</option>
    </select>
    <input type="date" class="e-delivery-date" value="${deliveryDate}" style="margin-top:4px;">
  `;
  row.querySelector('.td-qty').innerHTML = `<input type="number" class="e-qty" value="${qty}" style="text-align:right;">`;
  row.children[4].innerHTML = `
    <div class="edit-actions">
      <button class="btn-mini btn-mini-confirm" onclick="confirmEdit(this)">保存</button>
      <button class="btn-mini btn-mini-cancel" onclick="render()">取消</button>
    </div>`;
}

async function confirmEdit(btn) {
  const row = btn.closest('tr');
  const orderId = parseInt(row.dataset.id);
  const orderDate = row.querySelector('.e-date').value;
  const clientName = row.querySelector('.e-client').value;
  const deliveryType = row.querySelector('.e-delivery-type').value;
  const deliveryDate = row.querySelector('.e-delivery-date').value;
  const quantity = parseInt(row.querySelector('.e-qty').value) || 0;

  await fetch('../api/update_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id: orderId, client_name: clientName, order_date: orderDate,
      delivery_type: deliveryType || null,
      delivery_date: deliveryType === 'date' ? deliveryDate : null,
      quantity: quantity,
    }),
  });
  await loadDetail();
}

async function deleteOrder(orderId) {
  if (!confirm('この受注内訳を削除しますか？')) return;
  if (!confirm('本当に削除しますか？この操作は取り消せません。')) return;
  await fetch('../api/delete_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: orderId }),
  });
  await loadDetail();
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

loadDetail().then(() => loadStaff());
</script>
</body>
</html>
