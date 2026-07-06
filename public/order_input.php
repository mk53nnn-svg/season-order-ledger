<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$season = get_active_season();
$genres = get_genres();

if (!$season) {
    echo '現在アクティブなシーズンが設定されていません。';
    exit;
}

$pdo = get_pdo();
$clientsStmt = $pdo->query("SELECT id, name FROM clients ORDER BY name ASC");
$clients = $clientsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>受注入力 - シーズン受注台帳</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", "Yu Gothic", sans-serif; background: #f5f5f5; color: #222; }
  .page { max-width: 680px; margin: 0 auto; padding: 24px 16px; }
  .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
  .header h1 { font-size: 18px; font-weight: 600; }
  .header .season { font-size: 13px; color: #666; background: #fff; padding: 4px 10px; border-radius: 999px; }
  .card { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
  .section-label { font-size: 12px; font-weight: 600; color: #888; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.04em; }
  .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .field { display: flex; flex-direction: column; gap: 4px; margin-bottom: 12px; }
  .field label { font-size: 13px; color: #555; }
  .field input, .field select { height: 38px; border-radius: 8px; border: 1px solid #ccc; padding: 0 10px; font-size: 14px; background: #fafafa; }
  .field input:focus, .field select:focus { outline: none; border-color: #4a90d9; background: #fff; }
  .client-section { display: flex; flex-direction: column; gap: 8px; }
  .client-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .client-note { font-size: 11px; color: #999; }
  .delivery-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
  .delivery-row select { height: 38px; border-radius: 8px; border: 1px solid #ccc; padding: 0 8px; font-size: 14px; background: #fafafa; }
  .item-row { display: grid; grid-template-columns: 1fr 1fr 90px 32px; gap: 8px; align-items: center; margin-bottom: 10px; }
  .item-row select, .item-row input { height: 36px; border-radius: 8px; border: 1px solid #ccc; padding: 0 8px; font-size: 13px; background: #fafafa; }
  .del-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #ccc; background: #fff; color: #999; cursor: pointer; font-size: 16px; }
  .add-btn { font-size: 12px; color: #2b6cb0; border: 1px solid #2b6cb0; background: #eef5fc; border-radius: 8px; padding: 6px 12px; cursor: pointer; margin-bottom: 4px; }
  .col-labels { display: grid; grid-template-columns: 1fr 1fr 90px 32px; gap: 8px; margin-bottom: 4px; }
  .col-labels span { font-size: 11px; color: #999; }
  .actions { display: flex; gap: 10px; margin-top: 20px; }
  .btn-primary { flex: 1; height: 44px; border-radius: 8px; border: none; background: #222; color: #fff; font-size: 15px; font-weight: 600; cursor: pointer; }
  .btn-secondary { height: 44px; padding: 0 20px; border-radius: 8px; border: 1px solid #ccc; background: #fff; color: #555; font-size: 14px; cursor: pointer; }
  .msg { margin-top: 14px; font-size: 13px; padding: 10px 14px; border-radius: 8px; display: none; }
  .msg.success { background: #e6f4ea; color: #1e7e34; display: block; }
  .msg.error { background: #fdecea; color: #c0392b; display: block; }
</style>
</head>
<body>
<div style="padding:8px 16px;background:#fff;border-bottom:1px solid #eee;"><a href="index.php" style="font-size:12px;color:#888;text-decoration:none;">&laquo; ホーム</a></div>
<div class="page">
  <div class="header">
    <h1>受注入力</h1>
    <span class="season"><?= htmlspecialchars($season['name']) ?></span>
  </div>

  <form id="order-form">
    <div class="card">
      <div class="section-label">基本情報</div>

      <div class="field">
        <label>取引先</label>
        <div class="client-section">
          <div class="client-row">
            <select id="client_select">
              <option value="">-- プルダウンから選択 --</option>
              <?php foreach ($clients as $c): ?>
              <option value="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="text" id="client_name" placeholder="または自由入力">
          </div>
          <span class="client-note">※プルダウンで選択した場合は自由入力欄より優先されます</span>
        </div>
      </div>

      <div class="row2">
        <div class="field">
          <label>受注日</label>
          <input type="date" id="order_date" required>
        </div>
        <div class="field">
          <label>納期（空欄可）</label>
          <div class="delivery-row">
            <select id="delivery_month">
              <option value="">月を選択</option>
              <option value="11">11月</option>
              <option value="12">12月</option>
              <option value="1">1月</option>
              <option value="2">2月</option>
              <option value="3">3月</option>
              <option value="4">4月</option>
            </select>
            <select id="delivery_period">
              <option value="">時期を選択</option>
              <option value="即納">即納</option>
              <option value="初旬">初旬</option>
              <option value="中旬">中旬</option>
              <option value="下旬">下旬</option>
            </select>
            <input type="date" id="delivery_date" placeholder="または日付指定">
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="section-label">注文商品</div>
      <div class="col-labels">
        <span>ジャンル</span><span>商品</span><span>受注数</span><span></span>
      </div>
      <div id="item-list"></div>
      <button type="button" class="add-btn" id="add-row-btn">＋ 行を追加</button>
    </div>

    <div class="actions">
      <button type="button" class="btn-secondary" id="clear-btn">クリア</button>
      <button type="submit" class="btn-primary">登録する</button>
    </div>
    <div class="msg" id="msg"></div>
  </form>
</div>

<script>
const SEASON_ID = <?= (int)$season['id'] ?>;
const GENRES = <?= json_encode($genres, JSON_UNESCAPED_UNICODE) ?>;

document.getElementById('order_date').value = new Date().toISOString().slice(0, 10);

// 取引先：プルダウン選択時は自由入力をグレーアウト
document.getElementById('client_select').addEventListener('change', function() {
  const input = document.getElementById('client_name');
  if (this.value) {
    input.value = '';
    input.disabled = true;
    input.style.background = '#f0f0f0';
  } else {
    input.disabled = false;
    input.style.background = '';
  }
});

// 月プルダウンと日付入力の排他制御
document.getElementById('delivery_month').addEventListener('change', function() {
  if (this.value) {
    document.getElementById('delivery_date').value = '';
    document.getElementById('delivery_date').disabled = true;
    document.getElementById('delivery_date').style.background = '#f0f0f0';
  } else {
    document.getElementById('delivery_date').disabled = false;
    document.getElementById('delivery_date').style.background = '';
  }
});
document.getElementById('delivery_date').addEventListener('change', function() {
  if (this.value) {
    document.getElementById('delivery_month').value = '';
    document.getElementById('delivery_period').value = '';
    document.getElementById('delivery_month').disabled = true;
    document.getElementById('delivery_period').disabled = true;
  } else {
    document.getElementById('delivery_month').disabled = false;
    document.getElementById('delivery_period').disabled = false;
  }
});

function createItemRow() {
  const row = document.createElement('div');
  row.className = 'item-row';

  const genreSelect = document.createElement('select');
  genreSelect.innerHTML = '<option value="">ジャンル選択</option>' +
    GENRES.map(g => `<option value="${g.id}">${g.name}</option>`).join('');

  const productSelect = document.createElement('select');
  productSelect.disabled = true;
  productSelect.innerHTML = '<option value="">先にジャンルを選択</option>';

  const qtyInput = document.createElement('input');
  qtyInput.type = 'number';
  qtyInput.min = '1';
  qtyInput.placeholder = '0';

  const delBtn = document.createElement('button');
  delBtn.type = 'button';
  delBtn.className = 'del-btn';
  delBtn.textContent = '×';
  delBtn.onclick = () => {
    if (document.querySelectorAll('.item-row').length > 1) row.remove();
  };

  genreSelect.addEventListener('change', async () => {
    const genreId = genreSelect.value;
    if (!genreId) {
      productSelect.disabled = true;
      productSelect.innerHTML = '<option value="">先にジャンルを選択</option>';
      return;
    }
    productSelect.innerHTML = '<option value="">読み込み中...</option>';
    try {
      const res = await fetch(`../api/get_products.php?genre_id=${genreId}`);
      const products = await res.json();
      productSelect.innerHTML = '<option value="">商品を選択</option>' +
        products.map(p => `<option value="${p.id}">${p.product_name}</option>`).join('');
      productSelect.disabled = false;
    } catch (e) {
      productSelect.innerHTML = '<option value="">読み込み失敗</option>';
    }
  });

  row.appendChild(genreSelect);
  row.appendChild(productSelect);
  row.appendChild(qtyInput);
  row.appendChild(delBtn);
  return row;
}

document.getElementById('add-row-btn').addEventListener('click', () => {
  document.getElementById('item-list').appendChild(createItemRow());
});
document.getElementById('item-list').appendChild(createItemRow());
document.getElementById('clear-btn').addEventListener('click', () => location.reload());

document.getElementById('order-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const msg = document.getElementById('msg');
  msg.className = 'msg';

  // 取引先：プルダウン優先
  const clientSelect = document.getElementById('client_select').value;
  const clientInput = document.getElementById('client_name').value.trim();
  const clientName = clientSelect || clientInput;

  const orderDate = document.getElementById('order_date').value;
  const deliveryMonth = document.getElementById('delivery_month').value;
  const deliveryPeriod = document.getElementById('delivery_period').value;
  const deliveryDate = document.getElementById('delivery_date').value;

  if (!clientName) {
    msg.className = 'msg error';
    msg.textContent = '取引先を入力またはプルダウンから選択してください。';
    return;
  }
  if (!orderDate) {
    msg.className = 'msg error';
    msg.textContent = '受注日は必須です。';
    return;
  }

  // 納期の組み立て
  let deliveryType = null;
  let deliveryDateValue = null;
  if (deliveryDate) {
    deliveryType = 'date';
    deliveryDateValue = deliveryDate;
  } else if (deliveryMonth && deliveryPeriod) {
    deliveryType = deliveryMonth + '月' + deliveryPeriod;
  } else if (deliveryMonth) {
    deliveryType = deliveryMonth + '月';
  }
  // 空欄の場合はnullのまま

  const items = [];
  document.querySelectorAll('.item-row').forEach(row => {
    const selects = row.querySelectorAll('select');
    const qtyInput = row.querySelector('input');
    if (selects[1].value && qtyInput.value) {
      items.push({ product_id: parseInt(selects[1].value), quantity: parseInt(qtyInput.value) });
    }
  });

  if (items.length === 0) {
    msg.className = 'msg error';
    msg.textContent = '商品を1件以上入力してください。';
    return;
  }

  const payload = {
    season_id: SEASON_ID,
    client_name: clientName,
    order_date: orderDate,
    delivery_type: deliveryType,
    delivery_date: deliveryDateValue,
    items: items,
  };

  try {
    const res = await fetch('../api/save_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const result = await res.json();
    if (result.ok) {
      msg.className = 'msg success';
      msg.textContent = '登録しました。';
      setTimeout(() => location.reload(), 900);
    } else {
      msg.className = 'msg error';
      msg.textContent = '登録に失敗しました：' + (result.error || '不明なエラー');
    }
  } catch (err) {
    msg.className = 'msg error';
    msg.textContent = '通信エラーが発生しました。';
  }
});
</script>
</body>
</html>