<?php
/**
 * マスタ管理画面にシーズン管理タブを追加するスクリプト
 */
$file = __DIR__ . '/public/master.php';
$c = file_get_contents($file);

// タブにシーズン管理を追加
$c = str_replace(
  '<div class="tab" data-tab="client">取引先</div>',
  '<div class="tab" data-tab="client">取引先</div><div class="tab" data-tab="season">シーズン管理</div>',
  $c
);

// シーズン管理パネルを追加
$seasonPanel = '
  <!-- シーズン管理 -->
  <div class="panel" id="panel-season">
    <div class="add-row">
      <input type="text" id="new-season-name" placeholder="例：2026-2027シーズン">
      <input type="date" id="new-season-start">
      <input type="date" id="new-season-end">
      <button onclick="addSeason()">追加する</button>
    </div>
    <p class="note" style="margin-bottom:12px;">開始日：例 2026-11-01　終了日：例 2027-03-31</p>
    <table>
      <thead><tr><th>シーズン名</th><th>開始日</th><th>終了日</th><th>状態</th><th style="width:120px;"></th></tr></thead>
      <tbody id="season-tbody"></tbody>
    </table>
  </div>';

$c = str_replace(
  '<!-- 取引先 -->',
  $seasonPanel . "\n  <!-- 取引先 -->",
  $c
);

// シーズン管理のJavaScriptを追加
$seasonJs = '
async function loadSeasons() {
  const res = await fetch(\'../api/master_season.php\');
  const result = await res.json();
  if (!result.ok) return;
  const tbody = document.getElementById(\'season-tbody\');
  tbody.innerHTML = result.seasons.map(s => `
    <tr>
      <td>${escapeHtml(s.name)}</td>
      <td>${s.start_date}</td>
      <td>${s.end_date}</td>
      <td>${s.is_active == 1 ? \'<span style="color:#1e7e34;font-weight:600;">使用中</span>\' : \'\'}</td>
      <td>
        ${s.is_active != 1 ? `<button class="btn-mini btn-edit" onclick="activateSeason(${s.id})">切り替える</button>` : \'\'}
      </td>
    </tr>
  `).join(\'\');
}

async function addSeason() {
  const name = document.getElementById(\'new-season-name\').value.trim();
  const start = document.getElementById(\'new-season-start\').value;
  const end = document.getElementById(\'new-season-end\').value;
  if (!name || !start || !end) { showMsg(\'すべての項目を入力してください\', true); return; }
  const res = await fetch(\'../api/master_season.php\', {
    method: \'POST\', headers: {\'Content-Type\': \'application/json\'},
    body: JSON.stringify({action: \'add_season\', name, start_date: start, end_date: end}),
  });
  const result = await res.json();
  if (result.ok) {
    document.getElementById(\'new-season-name\').value = \'\';
    document.getElementById(\'new-season-start\').value = \'\';
    document.getElementById(\'new-season-end\').value = \'\';
    showMsg(\'シーズンを追加しました\');
    await loadSeasons();
  } else {
    showMsg(result.error || \'追加に失敗しました\', true);
  }
}

async function activateSeason(id) {
  if (!confirm(\'このシーズンに切り替えますか？\')) return;
  const res = await