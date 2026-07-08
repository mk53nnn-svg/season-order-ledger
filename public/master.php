<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>マスタ管理 - シーズン受注台帳</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", "Yu Gothic", sans-serif; background: #f5f5f5; color: #222; }
  .page { max-width: 760px; margin: 0 auto; padding: 24px 16px; }
  h1 { font-size: 18px; font-weight: 600; margin-bottom: 16px; }
  .tabs { display: flex; gap: 0; border-bottom: 1px solid #ddd; margin-bottom: 18px; }
  .tab { padding: 8px 18px; font-size: 14px; color: #888; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; }
  .tab.active { color: #222; font-weight: 600; border-bottom-color: #222; }
  .panel { display: none; }
  .panel.active { display: block; }
  .add-row { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
  .add-row input, .add-row select { height: 38px; border-radius: 8px; border: 1px solid #ccc; padding: 0 10px; font-size: 14px; flex: 1; min-width: 120px; }
  .add-row button { height: 38px; padding: 0 16px; border-radius: 8px; border: none; background: #222; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer; }
  table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05); font-size: 13px; }
  th { text-align: left; font-size: 11px; color: #888; padding: 8px 12px; border-bottom: 1px solid #eee; }
  td { padding: 9px 12px; border-bottom: 1px solid #eee; }
  .row-actions { display: flex; gap: 6px; }
  .btn-mini { font-size: 11px; padding: 4px 10px; border-radius: 6px; border: none; cursor: pointer; }
  .btn-edit { background: #eef5fc; color: #2b6cb0; border: 1px solid #2b6cb0; }
  .btn-delete { background: #fdecea; color: #c0392b; border: 1px solid #c0392b; }
  .btn-view { background: #f0f0f0; color: #555; border: 1px solid #ccc; }
  .btn-confirm { background: #222; color: #fff; }
  .btn-cancel { background: #fff; border: 1px solid #ccc; color: #888; }
  input.inline-edit { height: 30px; border-radius: 6px; border: 1px solid #ccc; padding: 0 8px; font-size: 13px; width: 100%; }
  select.inline-edit { height: 30px; border-radius: 6px; border: 1px solid #ccc; font-size: 13px; width: 100%; }
  .note { font-size: 12px; color: #999; margin-bottom: 12px; }
  .drag-handle { cursor: grab; color: #ccc; font-size: 16px; padding: 0 6px; user-select: none; }
  .drag-handle:active { cursor: grabbing; }
  tr.dragging { opacity: 0.4; }
  tr.drag-over { border-top: 2px solid #2b6cb0; }
  .msg { margin-top: 12px; font-size: 13px; padding: 10px 14px; border-radius: 8px; display: none; }
  .msg.success { background: #e6f4ea; color: #1e7e34; display: block; }
  .msg.error { background: #fdecea; color: #c0392b; display: block; }
  .client-name-link { color: #2b6cb0; cursor: pointer; text-decoration: underline; }
  .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; }
  .modal-overlay.show { display: flex; align-items: center; justify-content: center; }
  .modal { background: #fff; border-radius: 12px; padding: 24px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; }
  .modal h2 { font-size: 16px; font-weight: 600; margin-bottom: 16px; }
  .modal table { font-size: 13px; }
  .modal-close { margin-top: 16px; height: 36px; padding: 0 20px; border-radius: 8px; border: 1px solid #ccc; background: #fff; cursor: pointer; font-size: 13px; }
</style>
</head>
<body>
<div style="padding:8px 16px;background:#fff;border-bottom:1px solid #eee;"><a href="index.php" style="font-size:12px;color:#888;text-decoration:none;">&laquo; ホーム</a></div>
<div class="page">
  <h1>マスタ管理</h1>
  <div class="tabs">
    <div class="tab active" data-tab="genre">ジャンル</div>
    <div class="tab" data-tab="product">商品</div>
    <div class="tab" data-tab="client">取引先</div>
    <div class="tab" data-tab="season">シーズン管理</div>
  </div>

  <!-- ジャンル -->
  <div class="panel active" id="panel-genre">
    <div class="add-row">
      <input type="text" id="new-genre-name" placeholder="新しいジャンル名（例：のり・はさみ）">
      <button onclick="addGenre()">追加する</button>
    </div>
    <table>
      <thead><tr><th style="width:30px;"></th><th>ジャンル名</th><th style="width:140px;"></th></tr></thead>
      <tbody id="genre-tbody"></tbody>
    </table>
  </div>

  <!-- 商品 -->
  <div class="panel" id="panel-product">
    <div class="add-row">
      <select id="new-product-genre"></select>
      <input type="text" id="new-product-name" placeholder="商品名">
      <input type="text" id="new-product-code" placeholder="商品コード">
      <button onclick="addProduct()">追加する</button>
    </div>
    <table>
      <thead><tr><th>ジャンル</th><th>商品名</th><th>コード</th><th style="width:140px;"></th></tr></thead>
      <tbody id="product-tbody"></tbody>
    </table>
  </div>

  <!-- 取引先 -->
  <div class="panel" id="panel-client">
    <div class="add-row">
      <input type="text" id="new-client-name" placeholder="取引先名を入力">
      <button onclick="addClient()">追加する</button>
    </div>
    <p class="note">取引先名をクリックすると今シーズンの受注一覧を表示します。</p>
    <table>
      <thead><tr><th style="width:30px;"></th><th>取引先名</th><th style="width:160px;"></th></tr></thead>
      <tbody id="client-tbody"></tbody>
    </table>
  </div>

  <!-- シーズン管理 -->
  <div class="panel" id="panel-season">
    <div class="add-row">
      <input type="text" id="new-season-name" placeholder="例：2026-2027シーズン">
      <input type="date" id="new-season-start">
      <input type="date" id="new-season-end">
      <button onclick="addSeason()">追加する</button>
    </div>
    <p class="note" style="margin-bottom:12px;">開始日例：2026-11-01　終了日例：2027-03-31</p>
    <table>
      <thead><tr><th>シーズン名</th><th>開始日</th><th>終了日</th><th>状態</th><th style="width:160px;"></th></tr></thead>
      <tbody id="season-tbody"></tbody>
    </table>
  </div>

  <div class="msg" id="msg"></div>
</div>

<!-- 受注一覧モーダル -->
<div class="modal-overlay" id="client-modal">
  <div class="modal">
    <h2 id="modal-title"></h2>
    <table>
      <thead><tr><th>受注日</th><th>商品名</th><th>受注数</th><th>納期</th></tr></thead>
      <tbody id="modal-tbody"></tbody>
    </table>
    <button class="modal-close" onclick="closeModal()">閉じる</button>
  </div>
</div>

<script>
let masterData = { genres: [], products: [] };

function showMsg(text, isError = false) {
  const msg = document.getElementById('msg');
  msg.className = 'msg ' + (isError ? 'error' : 'success');
  msg.textContent = text;
  setTimeout(() => { msg.className = 'msg'; }, 2000);
}

document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
  });
});

async function loadMaster() {
  const res = await fetch('../api/master_genre_product.php');
  const result = await res.json();
  if (!result.ok) { showMsg('読み込みに失敗しました', true); return; }
  masterData = result;
  renderGenres();
  renderProducts();
  renderProductGenreSelect();
}

function renderGenres() {
  const tbody = document.getElementById('genre-tbody');
  const activeGenres = masterData.genres.filter(g => g.is_active == 1);
  tbody.innerHTML = activeGenres.map(g => `
    <tr data-id="${g.id}" draggable="true">
      <td><span class="drag-handle">&#9776;</span></td>
      <td class="td-name">${escapeHtml(g.name)}</td>
      <td><div class="row-actions">
        <button class="btn-mini btn-edit" onclick="editGenre(this)">編集</button>
        <button class="btn-mini btn-delete" onclick="deleteGenre(${g.id})">削除</button>
      </div></td>
    </tr>`).join('');
}

function renderProducts() {
  const tbody = document.getElementById('product-tbody');
  const activeProducts = masterData.products.filter(p => p.is_active == 1);
  tbody.innerHTML = activeProducts.map(p => `
    <tr data-id="${p.id}" data-genre-id="${p.genre_id}">
      <td class="td-genre">${escapeHtml(p.genre_name)}</td>
      <td class="td-name">${escapeHtml(p.product_name)}</td>
      <td class="td-code">${escapeHtml(p.product_code)}</td>
      <td><div class="row-actions">
        <button class="btn-mini btn-edit" onclick="editProduct(this)">編集</button>
        <button class="btn-mini btn-delete" onclick="deleteProduct(${p.id})">削除</button>
      </div></td>
    </tr>`).join('');
}

function renderProductGenreSelect() {
  const select = document.getElementById('new-product-genre');
  const activeGenres = masterData.genres.filter(g => g.is_active == 1);
  select.innerHTML = activeGenres.map(g => `<option value="${g.id}">${escapeHtml(g.name)}</option>`).join('');
}

async function addClient() {
  const name = document.getElementById('new-client-name').value.trim();
  if (!name) { showMsg('取引先名を入力してください', true); return; }
  const res = await fetch('../api/update_client.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'add_client', name}),
  });
  const result = await res.json();
  if (result.ok) {
    document.getElementById('new-client-name').value = '';
    showMsg('取引先を追加しました');
    await loadClients();
  } else { showMsg(result.error || '追加に失敗しました', true); }
}

async function loadClients() {
  const res = await fetch('../api/get_clients.php');
  const result = await res.json();
  const tbody = document.getElementById('client-tbody');
  if (!result.ok || result.clients.length === 0) {
    tbody.innerHTML = '<tr><td colspan="2" style="color:#999;">まだ取引先データがありません</td></tr>';
    return;
  }
  tbody.innerHTML = result.clients.map(c => `
    <tr data-id="${c.id}" data-name="${escapeHtml(c.name)}" draggable="true">
      <td><span class="drag-handle">&#9776;</span></td>
      <td><span class="client-name-link" onclick="showClientOrders('${escapeHtml(c.name)}')">${escapeHtml(c.name)}</span></td>
      <td><div class="row-actions">
        <button class="btn-mini btn-edit" onclick="editClient(this)">編集</button>
        <button class="btn-mini btn-delete" onclick="deleteClient(${c.id}, '${escapeHtml(c.name)}')">削除</button>
      </div></td>
    </tr>`).join('');
}

async function showClientOrders(clientName) {
  const seasonRes = await fetch('../api/get_season.php');
  const seasonData = await seasonRes.json();
  if (!seasonData.ok) return;
  const seasonId = seasonData.season.id;
  const res = await fetch(`../api/get_client_orders.php?client_name=${encodeURIComponent(clientName)}&season_id=${seasonId}`);
  const result = await res.json();
  if (!result.ok) return;
  document.getElementById('modal-title').textContent = clientName + ' の受注一覧（今シーズン）';
  const tbody = document.getElementById('modal-tbody');
  if (result.orders.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" style="color:#999;">受注データがありません</td></tr>';
  } else {
    tbody.innerHTML = result.orders.map(o => `
      <tr>
        <td>${o.order_date}</td>
        <td>${escapeHtml(o.product_name)}</td>
        <td style="text-align:right;">${o.quantity}</td>
        <td>${o.delivery_label ? escapeHtml(o.delivery_label) : '−'}</td>
      </tr>`).join('') +
      `<tr style="font-weight:600;background:#fafafa;">
        <td colspan="2" style="text-align:right;color:#888;font-size:12px;">合計</td>
        <td style="text-align:right;">${result.total}</td>
        <td></td>
      </tr>`;
  }
  document.getElementById('client-modal').classList.add('show');
}

function closeModal() {
  document.getElementById('client-modal').classList.remove('show');
}

function editClient(btn) {
  const row = btn.closest('tr');
  const name = row.dataset.name;
  const id = row.dataset.id;
  row.querySelector('.client-name-link').outerHTML = `<input type="text" class="inline-edit e-client-name" value="${escapeHtml(name)}">`;
  row.cells[1].innerHTML = `<div class="row-actions">
    <button class="btn-mini btn-confirm" onclick="confirmEditClient(this, ${id}, '${escapeHtml(name)}')">保存</button>
    <button class="btn-mini btn-cancel" onclick="loadClients()">取消</button>
  </div>`;
}

async function confirmEditClient(btn, id, oldName) {
  const row = btn.closest('tr');
  const newName = row.querySelector('.e-client-name').value.trim();
  if (!newName) return;
  const res = await fetch('../api/update_client.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'update_client', id, name: newName, old_name: oldName}),
  });
  const result = await res.json();
  if (result.ok) { showMsg('更新しました'); await loadClients(); }
  else { showMsg(result.error || '更新に失敗しました', true); }
}

async function deleteClient(id, name) {
  if (!confirm(`「${name}」を削除しますか？\n※受注データは残ります`)) return;
  const res = await fetch('../api/update_client.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'delete_client', id}),
  });
  const result = await res.json();
  if (result.ok) { showMsg('削除しました'); await loadClients(); }
  else { showMsg(result.error || '削除に失敗しました', true); }
}

async function addGenre() {
  const name = document.getElementById('new-genre-name').value.trim();
  if (!name) return;
  const res = await fetch('../api/master_genre_product.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'add_genre', name}),
  });
  const result = await res.json();
  if (result.ok) { document.getElementById('new-genre-name').value = ''; showMsg('ジャンルを追加しました'); await loadMaster(); }
  else { showMsg(result.error || '追加に失敗しました', true); }
}

function editGenre(btn) {
  const row = btn.closest('tr');
  const name = row.querySelector('.td-name').textContent;
  row.querySelector('.td-name').innerHTML = `<input type="text" class="inline-edit e-name" value="${escapeHtml(name)}">`;
  row.cells[2].innerHTML = `<div class="row-actions">
    <button class="btn-mini btn-confirm" onclick="confirmEditGenre(this)">保存</button>
    <button class="btn-mini btn-cancel" onclick="loadMaster()">取消</button>
  </div>`;
}

async function confirmEditGenre(btn) {
  const row = btn.closest('tr');
  const id = parseInt(row.dataset.id);
  const name = row.querySelector('.e-name').value.trim();
  if (!name) return;
  await fetch('../api/master_genre_product.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'update_genre', id, name}),
  });
  showMsg('更新しました');
  await loadMaster();
}

async function deleteGenre(id) {
  if (!confirm('このジャンルを削除しますか？')) return;
  await fetch('../api/master_genre_product.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'delete_genre', id}),
  });
  showMsg('削除しました');
  await loadMaster();
}

async function addProduct() {
  const genreId = parseInt(document.getElementById('new-product-genre').value);
  const code = document.getElementById('new-product-code').value.trim();
  const name = document.getElementById('new-product-name').value.trim();
  if (!genreId || !code || !name) { showMsg('すべての項目を入力してください', true); return; }
  const res = await fetch('../api/master_genre_product.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'add_product', genre_id: genreId, product_code: code, product_name: name}),
  });
  const result = await res.json();
  if (result.ok) {
    document.getElementById('new-product-code').value = '';
    document.getElementById('new-product-name').value = '';
    showMsg('商品を追加しました');
    await loadMaster();
  } else { showMsg(result.error || '追加に失敗しました', true); }
}

function editProduct(btn) {
  const row = btn.closest('tr');
  const genreId = row.dataset.genreId;
  const name = row.querySelector('.td-name').textContent;
  const code = row.querySelector('.td-code').textContent;
  const genreOptions = masterData.genres.filter(g => g.is_active == 1)
    .map(g => `<option value="${g.id}" ${g.id == genreId ? 'selected' : ''}>${escapeHtml(g.name)}</option>`).join('');
  row.querySelector('.td-genre').innerHTML = `<select class="inline-edit e-genre">${genreOptions}</select>`;
  row.querySelector('.td-name').innerHTML = `<input type="text" class="inline-edit e-name" value="${escapeHtml(name)}">`;
  row.querySelector('.td-code').innerHTML = `<input type="text" class="inline-edit e-code" value="${escapeHtml(code)}">`;
  row.cells[3].innerHTML = `<div class="row-actions">
    <button class="btn-mini btn-confirm" onclick="confirmEditProduct(this)">保存</button>
    <button class="btn-mini btn-cancel" onclick="loadMaster()">取消</button>
  </div>`;
}

async function confirmEditProduct(btn) {
  const row = btn.closest('tr');
  const id = parseInt(row.dataset.id);
  const genreId = parseInt(row.querySelector('.e-genre').value);
  const name = row.querySelector('.e-name').value.trim();
  const code = row.querySelector('.e-code').value.trim();
  if (!name || !code) return;
  await fetch('../api/master_genre_product.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'update_product', id, genre_id: genreId, product_code: code, product_name: name}),
  });
  showMsg('更新しました');
  await loadMaster();
}

async function deleteProduct(id) {
  if (!confirm('この商品を削除しますか？（過去の受注データは残ります）')) return;
  await fetch('../api/master_genre_product.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'delete_product', id}),
  });
  showMsg('削除しました');
  await loadMaster();
}

function editSeason(btn, id) {
  const row = btn.closest('tr');
  const name = row.cells[0].textContent;
  const start = row.cells[1].textContent;
  const end = row.cells[2].textContent;
  row.cells[0].innerHTML = `<input type="text" class="inline-edit e-season-name" value="${escapeHtml(name)}">`;
  row.cells[1].innerHTML = `<input type="date" class="inline-edit e-season-start" value="${start}">`;
  row.cells[2].innerHTML = `<input type="date" class="inline-edit e-season-end" value="${end}">`;
  row.cells[4].innerHTML = `<div class="row-actions">
    <button class="btn-mini btn-confirm" onclick="confirmEditSeason(this, ${id})">保存</button>
    <button class="btn-mini btn-cancel" onclick="loadSeasons()">取消</button>
  </div>`;
}

async function confirmEditSeason(btn, id) {
  const row = btn.closest('tr');
  const name = row.querySelector('.e-season-name').value.trim();
  const start = row.querySelector('.e-season-start').value;
  const end = row.querySelector('.e-season-end').value;
  if (!name || !start || !end) { showMsg('すべての項目を入力してください', true); return; }
  const res = await fetch('../api/master_season.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'update_season', id, name, start_date: start, end_date: end}),
  });
  const result = await res.json();
  if (result.ok) { showMsg('更新しました'); await loadSeasons(); }
  else { showMsg(result.error || '更新に失敗しました', true); }
}

async function loadSeasons() {
  const res = await fetch('../api/master_season.php');
  const result = await res.json();
  if (!result.ok) return;
  const tbody = document.getElementById('season-tbody');
  tbody.innerHTML = result.seasons.map(s => `
    <tr>
      <td>${escapeHtml(s.name)}</td>
      <td>${s.start_date}</td>
      <td>${s.end_date}</td>
      <td>${s.is_active == 1 ? '<span style="color:#1e7e34;font-weight:600;">使用中</span>' : ''}</td>
      <td><div class="row-actions">
        <button class="btn-mini btn-edit" onclick="editSeason(this, ${s.id})">編集</button>
        ${s.is_active != 1 ? `<button class="btn-mini btn-edit" onclick="activateSeason(${s.id})">切替</button>` : ''}
        ${s.is_active != 1 ? `<button class="btn-mini btn-delete" onclick="deleteSeason(${s.id}, '${escapeHtml(s.name)}')">削除</button>` : ''}
      </div></td>
    </tr>`).join('');
}

async function addSeason() {
  const name = document.getElementById('new-season-name').value.trim();
  const start = document.getElementById('new-season-start').value;
  const end = document.getElementById('new-season-end').value;
  if (!name || !start || !end) { showMsg('すべての項目を入力してください', true); return; }
  const res = await fetch('../api/master_season.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'add_season', name, start_date: start, end_date: end}),
  });
  const result = await res.json();
  if (result.ok) {
    document.getElementById('new-season-name').value = '';
    document.getElementById('new-season-start').value = '';
    document.getElementById('new-season-end').value = '';
    showMsg('シーズンを追加しました');
    await loadSeasons();
  } else { showMsg(result.error || '追加に失敗しました', true); }
}

async function activateSeason(id) {
  if (!confirm('このシーズンに切り替えますか？')) return;
  const res = await fetch('../api/master_season.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'activate_season', id}),
  });
  const result = await res.json();
  if (result.ok) { showMsg('シーズンを切り替えました'); await loadSeasons(); }
  else { showMsg(result.error || '切り替えに失敗しました', true); }
}

async function deleteSeason(id, name) {
  if (!confirm(`「${name}」を削除しますか？`)) return;
  if (!confirm('本当に削除しますか？この操作は取り消せません。')) return;
  const res = await fetch('../api/master_season.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'delete_season', id}),
  });
  const result = await res.json();
  if (result.ok) { showMsg('削除しました'); await loadSeasons(); }
  else { showMsg(result.error || '削除に失敗しました', true); }
}

function initDragAndDrop(tbodyId, saveAction, apiUrl) {
  const tbody = document.getElementById(tbodyId);
  if (!tbody) return;
  let dragSrc = null;

  tbody.addEventListener('dragstart', e => {
    dragSrc = e.target.closest('tr');
    if (!dragSrc) return;
    dragSrc.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
  });
  tbody.addEventListener('dragover', e => {
    e.preventDefault();
    const target = e.target.closest('tr');
    if (!target || target === dragSrc) return;
    document.querySelectorAll(`#${tbodyId} tr`).forEach(r => r.classList.remove('drag-over'));
    target.classList.add('drag-over');
    e.dataTransfer.dropEffect = 'move';
  });
  tbody.addEventListener('dragleave', e => {
    const target = e.target.closest('tr');
    if (target) target.classList.remove('drag-over');
  });
  tbody.addEventListener('drop', async e => {
    e.preventDefault();
    const target = e.target.closest('tr');
    if (!target || target === dragSrc) return;
    document.querySelectorAll(`#${tbodyId} tr`).forEach(r => r.classList.remove('drag-over'));
    dragSrc.classList.remove('dragging');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const srcIdx = rows.indexOf(dragSrc);
    const tgtIdx = rows.indexOf(target);
    if (srcIdx < tgtIdx) {
      target.after(dragSrc);
    } else {
      target.before(dragSrc);
    }
    const ids = Array.from(tbody.querySelectorAll('tr')).map(r => parseInt(r.dataset.id)).filter(Boolean);
    await fetch(apiUrl, {
      method: 'POST', headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({action: saveAction, ids}),
    });
  });
  tbody.addEventListener('dragend', e => {
    document.querySelectorAll(`#${tbodyId} tr`).forEach(r => {
      r.classList.remove('dragging');
      r.classList.remove('drag-over');
    });
  });
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

loadMaster().then(() => initDragAndDrop('genre-tbody', 'reorder_genres', '../api/master_genre_product.php'));
loadClients().then(() => initDragAndDrop('client-tbody', 'reorder_clients', '../api/update_client.php'));
loadSeasons();
</script>
</body>
</html>