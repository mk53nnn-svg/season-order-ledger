<?php
$file = __DIR__ . '/public/product_detail.php';
$c = file_get_contents($file);

// 1. シーズン開始時在庫パネルを削除
$c = preg_replace(
    '/<div class="stock-panel">.*?<\/div>\s*<\/div>/s',
    '',
    $c
);

// 2. 在庫ラベルを修正
$c = str_replace(
    '<div class="label">在庫（開始時+発注-受注）</div>',
    '<div class="label">在庫（発注-受注）</div>',
    $c
);

// 3. 発注履歴の行表示を棚卸対応に
$c = str_replace(
    "    : d.purchase_orders.map(po => `
        <tr data-po-id=\"\${po.id}\">
          <td class=\"po-date\">\${po.order_date}</td>
          <td class=\"num po-qty\">\${po.quantity}</td>
          <td colspan=\"2\">
            <div class=\"row-actions\">
              <button class=\"edit-btn\" onclick=\"startEditPo(this, \${po.id})\">編集</button>
              <button class=\"delete-btn\" onclick=\"deletePo(\${po.id})\">削除</button>
            </div>
          </td>
        </tr>`).join('') +",
    "    : d.purchase_orders.map(po => `
        <tr data-po-id=\"\${po.id}\" \${po.is_tanoroshi ? 'style=\"background:#f0f5ff;\"' : ''}>
          <td class=\"po-date\">\${po.is_tanoroshi ? '<span style=\"font-size:11px;color:#2b6cb0;font-weight:600;\">棚卸在庫</span>' : po.order_date}</td>
          <td class=\"num po-qty\">\${po.quantity}</td>
          <td colspan=\"2\">
            <div class=\"row-actions\">
              <button class=\"edit-btn\" onclick=\"startEditPo(this, \${po.id}, \${po.is_tanoroshi})\">編集</button>
              <button class=\"delete-btn\" onclick=\"deletePo(\${po.id})\">削除</button>
            </div>
          </td>
        </tr>`).join('') +",
    $c
);

// 4. startEditPo関数を棚卸対応に
$c = str_replace(
    'function startEditPo(btn, poId) {
  const row = btn.closest(\'tr\');
  const date = row.querySelector(\'.po-date\').textContent;
  const qty = row.querySelector(\'.po-qty\').textContent;
  row.classList.add(\'edit-row\');
  row.querySelector(\'.po-date\').innerHTML = `<input type="date" class="e-po-date" value="${date}">`;',
    'function startEditPo(btn, poId, isTanoroshi) {
  const row = btn.closest(\'tr\');
  const date = isTanoroshi ? \'\' : row.querySelector(\'.po-date\').textContent;
  const qty = row.querySelector(\'.po-qty\').textContent;
  row.classList.add(\'edit-row\');
  row.querySelector(\'.po-date\').innerHTML = isTanoroshi
    ? \'<span style="font-size:11px;color:#2b6cb0;font-weight:600;">棚卸在庫</span>\'
    : `<input type="date" class="e-po-date" value="${date}">`;',
    $c
);

// 5. saveInitialStock関数を削除
$c = preg_replace(
    '/async function saveInitialStock\(\).*?}\n\n/s',
    '',
    $c
);

file_put_contents($file, $c);
echo "done\n";