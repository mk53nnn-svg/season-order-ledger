<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$season = get_active_season();
$genres = get_genres();

if (!$season) {
    echo '現在アクティブなシーズンが設定されていません。先にシーズンを作成してください。';
    exit;
}
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
  .delivery-options { display: flex; gap: 8px; margin-top: 6px; flex-wrap: wrap; }
  .delivery-btn { flex: 1; min-width: 70px; height: 38px; border-radius: 8px; border: 1px solid #ccc; background: #fafafa; font-size: 13px; cursor: pointer; }
  .delivery-btn.active { background: #2b6cb0; color: #fff; border-color: #2b6cb0; }
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
<div class="page">
  <div class="header">
    <h1>受注入力</h1>
    <span class="season"><?= htmlspecialchars($season['name']) ?></span>
  </div>

  <form id="order-form">
    <div class="card">
      <div class="section-label">基本情報</div>
      <div class="row2">
        <div class="field">
          <label>取引先</label>
          <input type="text" id="client_name" placeholder="例：さくら保育園" required>
        </div>
        <div class="field">
          <label>受注日</label>
          <input type="date" id="order_date" required>
        </div>
      </div>

      <div class="field">
        <label>納期</label>
        <div class="row2">
          <input type="date" id="delivery_date">
          <div class="delivery-options" id="delivery-options">
            <button type="button" class="delivery-btn" data-value="即納">即納</button>
            <button type="button" class="delivery-btn" data-value="初旬">初旬</button>
            <button type="button" class="delivery-btn" data-value="中旬">中旬</button>
            <button type="button" class="delivery-btn" data-value="下旬">下旬</button>
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

// 受注日のデフォルトを今日にする
document.getElementById('order_date').value = new Date().toISOString().slice(0, 10);

// 納期ボタンの選択状態管理
let selectedDeliveryType = null;
const deliveryDateInput = document.getElementById('delivery_date');
document.querySelectorAll('.delivery-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.delivery-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selectedDeliveryType = btn.dataset.value;
    deliveryDateInput.value = '';
  });
});
deliveryDateInput.addEventListener('input', () => {
  if (deliveryDateInput.value) {
    selectedDeliveryType = null;
    document.querySelectorAll('.delivery-btn').forEach(b => b.classList.remove('active'));
  }
});

// 商品行の追加・削除
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
    productSelect.disabled = true;
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
// 初期1行
document.getElementById('item-list').appendChild(createItemRow());

document.getElementById('clear-btn').addEventListener('click', () => location.reload());

// 送信処理
document.getElementById('order-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const msg = document.getElementById('msg');
  msg.className = 'msg';
  msg.textContent = '';

  const clientName = document.getElementById('client_name').value.trim();
  const orderDate = document.getElementById('order_date').value;
  const deliveryDate = deliveryDateInput.value;

  if (!clientName || !orderDate) {
    msg.className = 'msg error';
    msg.textContent = '取引先と受注日は必須です。';
    return;
  }
  if (!deliveryDate && !selectedDeliveryType) {
    msg.className = 'msg error';
    msg.textContent = '納期（日付または即納/初旬/中旬/下旬）を指定してください。';
    return;
  }

  const items = [];
  document.querySelectorAll('.item-row').forEach(row => {
    const [genreSelect, productSelect, qtyInput] = row.querySelectorAll('select, input');
    if (productSelect.value && qtyInput.value) {
      items.push({ product_id: parseInt(productSelect.value), quantity: parseInt(qtyInput.value) });
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
    delivery_type: deliveryDate ? 'date' : selectedDeliveryType,
    delivery_date: deliveryDate || null,
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
