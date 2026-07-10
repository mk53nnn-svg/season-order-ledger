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
$clientsStmt = $pdo->query("SELECT id, name FROM clients ORDER BY display_order ASC, id ASC");
$clients = $clientsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="common.css">
<title>受注入力 - シーズン受注台帳</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html { overflow-y: scroll; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", "Yu Gothic", sans-serif; background: #f5f5f5; color: #222; }
  .page { max-width: 680px; margin: 0 auto; padding: 24px 16px; }
  .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
  .header h1 { font-size: 18px; font-weight: 600; }
  .header .season { font-size: 13px; color: #666; background: #fff; padding: 4px 10px; border-radius: 999px; }
  .card { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: visible; }
  #item-list { min-height: 60px; }
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
  .item-row { display: grid; grid-template-columns: 1fr 1fr 90px 32px; gap: 8px; align-items: start; margin-bottom: 10px; position: relative; }
  .item-row input { height: 36px; border-radius: 8px; border: 1px solid #ccc; padding: 0 8px; font-size: 13px; background: #fafafa; }
  .del-btn { width: 32px; height: 36px; border-radius: 8px; border: 1px solid #ccc; background: #fff; color: #999; cursor: pointer; font-size: 16px; margin-top: 0; }
  .add-btn { font-size: 12px; color: #2b6cb0; border: 1px solid #2b6cb0; background: #eef5fc; border-radius: 8px; padding: 6px 12px; cursor: pointer; margin-bottom: 4px; }
  .col-labels { display: grid; grid-template-columns: 1fr 1fr 90px 32px; gap: 8px; margin-bottom: 4px; }
  .col-labels span { font-size: 11px; color: #999; }
  .actions { display: flex; gap: 10px; margin-top: 20px; }
  .btn-primary { flex: 1; height: 44px; border-radius: 8px; border: none; background: #222; color: #fff; font-size: 15px; font-weight: 600; cursor: pointer; }
  .btn-secondary { height: 44px; padding: 0 20px; border-radius: 8px; border: 1px solid #ccc; background: #fff; color: #555; font-size: 14px; cursor: pointer; }
  .msg { margin-top: 14px; font-size: 13px; padding: 10px 14px; border-radius: 8px; display: none; }
  .msg.success { background: #e6f4ea; color: #1e7e34; display: block; }
  .msg.error { background: #fdecea; color: #c0392b; display: block; }

  /* カスタムドロップダウン */
  .custom-select { position: relative; width: 100%; }
  .custom-select { position: relative; width: 100%; }
  .custom-select-trigger { height: 38px; border-radius: 8px; border: 1px solid #ccc; padding: 0 10px; font-size: 14px; color: #222; background: #fafafa; display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none; }
  .custom-select-trigger:hover { border-color: #4a90d9; }
  .custom-select-trigger.open { border-color: #4a90d9; background: #fff; }
  .custom-select-trigger .arrow { font-size: 10px; color: #888; }
  .custom-select-dropdown { position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #fff; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.12); z-index: 9999; max-height: 240px; overflow-y: auto; display: none; }
  .custom-select-dropdown.open { display: block; }
  .custom-select-option { padding: 8px 12px; font-size: 14px; color: #222; cursor: pointer; }
  .custom-select-option:hover { background: #f0f5ff; }
  .custom-select-option.selected { background: #eef5fc; color: #2b6cb0; font-weight: 600; }
  .custom-select-option.placeholder { color: #555; }
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
      <button type="submit" class="btn-primary">記帳する</button>
    </div>
    <div class="msg" id="msg"></div>
  </form>
</div>

<!-- 未登録取引先確認モーダル -->
<div id="unregistered-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:24px;max-width:400px;width:90%;">
    <h2 style="font-size:16px;font-weight:600;margin-bottom:12px;">取引先が未登録です</h2>
    <p id="unregistered-msg" style="font-size:14px;color:#555;margin-bottom:20px;"></p>
    <div style="display:flex;gap:10px;">
      <button id="btn-register-and-kicho" style="flex:1;height:40px;border-radius:8px;border:none;background:#222;color:#fff;font-size:13px;font-weight:600;cursor:pointer;">登録して記帳</button>
      <button id="btn-kicho-only" style="flex:1;height:40px;border-radius:8px;border:1px solid #ccc;background:#fff;color:#555;font-size:13px;cursor:pointer;">登録せずに記帳</button>
    </div>
    <button id="btn-cancel-modal" style="width:100%;margin-top:10px;height:36px;border-radius:8px;border:1px solid #ccc;background:#fff;color:#999;font-size:13px;cursor:pointer;">キャンセル</button>
  </div>
</div>

<script>
const SEASON_ID = <?= (int)$season['id'] ?>;
const GENRES = <?= json_encode($genres, JSON_UNESCAPED_UNICODE) ?>;

document.getElementById('order_date').value = new Date().toISOString().slice(0, 10);

// 取引先プルダウン選択時に自由入力をグレーアウト
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

// カスタムドロップダウン作成
function createCustomSelect(options, placeholder, onChange) {
  const wrapper = document.createElement('div');
  wrapper.className = 'custom-select';

  const trigger = document.createElement('div');
  trigger.className = 'custom-select-trigger';
  trigger.innerHTML = `<span class="selected-label">${placeholder}</span><span class="arrow">▼</span>`;

  const dropdown = document.createElement('div');
  dropdown.className = 'custom-select-dropdown';

  const placeholderOpt = document.createElement('div');
  placeholderOpt.className = 'custom-select-option placeholder';
  placeholderOpt.textContent = placeholder;
  placeholderOpt.addEventListener('click', () => {
    trigger.querySelector('.selected-label').textContent = placeholder;
    trigger.classList.remove('open');
    dropdown.classList.remove('open');
    wrapper._value = '';
    onChange('');
  });
  dropdown.appendChild(placeholderOpt);

  options.forEach(opt => {
    const el = document.createElement('div');
    el.className = 'custom-select-option';
    el.textContent = opt.label;
    el.dataset.value = opt.value;
    el.addEventListener('click', () => {
      trigger.querySelector('.selected-label').textContent = opt.label;
      dropdown.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
      el.classList.add('selected');
      trigger.classList.remove('open');
      dropdown.classList.remove('open');
      wrapper._value = opt.value;
      onChange(opt.value);
    });
    dropdown.appendChild(el);
  });

  trigger.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = dropdown.classList.contains('open');
    // 他の全ドロップダウンを閉じる
    document.querySelectorAll('.custom-select-dropdown.open').forEach(d => {
      d.classList.remove('open');
      d.previousElementSibling.classList.remove('open');
    });
    if (!isOpen) {
      dropdown.classList.add('open');
      trigger.classList.add('open');
    }
  });

  wrapper._value = '';
  wrapper.appendChild(trigger);
  wrapper.appendChild(dropdown);
  return wrapper;
}

// ドロップダウンを閉じる
document.addEventListener('click', () => {
  document.querySelectorAll('.custom-select-dropdown.open').forEach(d => {
    d.classList.remove('open');
    d.previousElementSibling.classList.remove('open');
  });
});

function createItemRow() {
  const row = document.createElement('div');
  row.className = 'item-row';

  // ジャンルカスタムドロップダウン
  const genreOptions = GENRES.map(g => ({ value: String(g.id), label: g.name }));
  let productCustomSelect = null;

  const genreCustomSelect = createCustomSelect(genreOptions, 'ジャンル選択', async (genreId) => {
    if (productCustomSelect) {
      productCustomSelect.querySelector('.selected-label').textContent = '商品を選択';
      productCustomSelect._value = '';
      const dropdown = productCustomSelect.querySelector('.custom-select-dropdown');
      dropdown.innerHTML = '';
      const ph = document.createElement('div');
      ph.className = 'custom-select-option placeholder';
      ph.textContent = '商品を選択';
      dropdown.appendChild(ph);
    }

    if (!genreId) return;

    try {
      const res = await fetch(`../api/get_products.php?genre_id=${genreId}`);
      const products = await res.json();
      if (productCustomSelect) {
        const dropdown = productCustomSelect.querySelector('.custom-select-dropdown');
        dropdown.innerHTML = '';
        const ph = document.createElement('div');
        ph.className = 'custom-select-option placeholder';
        ph.textContent = '商品を選択';
        ph.addEventListener('click', () => {
          productCustomSelect.querySelector('.selected-label').textContent = '商品を選択';
          productCustomSelect._value = '';
          dropdown.classList.remove('open');
          productCustomSelect.querySelector('.custom-select-trigger').classList.remove('open');
        });
        dropdown.appendChild(ph);

        products.forEach(p => {
          const el = document.createElement('div');
          el.className = 'custom-select-option';
          el.textContent = p.product_name;
          el.dataset.value = p.id;
          el.addEventListener('click', () => {
            productCustomSelect.querySelector('.selected-label').textContent = p.product_name;
            dropdown.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            productCustomSelect._value = String(p.id);
            dropdown.classList.remove('open');
            productCustomSelect.querySelector('.custom-select-trigger').classList.remove('open');
          });
          dropdown.appendChild(el);
        });
      }
    } catch (e) {}
  });

  // 商品カスタムドロップダウン
  productCustomSelect = createCustomSelect([], '先にジャンルを選択', () => {});
  productCustomSelect.querySelector('.custom-select-trigger').style.pointerEvents = 'none';
  productCustomSelect.querySelector('.custom-select-trigger').style.opacity = '0.6';

  // ジャンル選択後に商品を有効化
  const origGenreOnChange = genreOptions;
  genreCustomSelect.querySelector('.custom-select-trigger').addEventListener('click', () => {
    setTimeout(() => {
      productCustomSelect.querySelector('.custom-select-trigger').style.pointerEvents = '';
      productCustomSelect.querySelector('.custom-select-trigger').style.opacity = '';
    }, 100);
  });

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

  row.appendChild(genreCustomSelect);
  row.appendChild(productCustomSelect);
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

  const items = [];
  document.querySelectorAll('.item-row').forEach(row => {
    const selects = row.querySelectorAll('.custom-select');
    const qtyInput = row.querySelector('input');
    const productId = selects[1] ? selects[1]._value : '';
    if (productId && qtyInput.value) {
      items.push({ product_id: parseInt(productId), quantity: parseInt(qtyInput.value) });
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

  
  const clientRes = await fetch('../api/get_clients.php');
  const clientData = await clientRes.json();
  const registeredNames = clientData.ok ? clientData.clients.map(c => c.name) : [];
  const isUnregistered = !registeredNames.includes(clientName);

  if (isUnregistered) {
    document.getElementById('unregistered-msg').textContent = `「${clientName}」は取引先マスタに未登録です。どちらで記帳しますか？`;
    const modal = document.getElementById('unregistered-modal');
    modal.style.display = 'flex';

    const choice = await new Promise(resolve => {
      document.getElementById('btn-register-and-kicho').onclick = () => { modal.style.display = 'none'; resolve('register'); };
      document.getElementById('btn-kicho-only').onclick = () => { modal.style.display = 'none'; resolve('kicho'); };
      document.getElementById('btn-cancel-modal').onclick = () => { modal.style.display = 'none'; resolve('cancel'); };
    });

    if (choice === 'cancel') return;

    if (choice === 'register') {
      await fetch('../api/update_client.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add_client', name: clientName }),
      });
    }
  }

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
