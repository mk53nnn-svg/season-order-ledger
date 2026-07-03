<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>シーズン受注台帳</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN", "Yu Gothic", sans-serif; background: #f5f5f5; color: #222; min-height: 100vh; display: flex; flex-direction: column; }
.header { background: #fff; padding: 20px 24px; border-bottom: 1px solid #eee; text-align: center; }
.header h1 { font-size: 20px; font-weight: 600; color: #333; }
.header .season { font-size: 13px; color: #888; margin-top: 4px; }
.main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 24px; }
.grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: 800px; width: 100%; }
.card { background: #fff; border-radius: 16px; padding: 40px 20px; text-align: center; text-decoration: none; color: #222; box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: transform 0.15s, box-shadow 0.15s; display: flex; flex-direction: column; align-items: center; gap: 16px; }
.card:hover { transform: translateY(-4px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
.card .icon { width: 72px; height: 72px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 36px; }
.card.order .icon { background: #eef5fc; }
.card.ledger .icon { background: #e6f4ea; }
.card.master .icon { background: #fff8e6; }
.card .label { font-size: 16px; font-weight: 600; line-height: 1.4; }
.footer { text-align: right; padding: 12px 20px; }
.footer a { font-size: 11px; color: #bbb; text-decoration: none; }
.footer a:hover { color: #888; }
</style>
</head>
<body>
<div class="header">
  <h1>シーズン受注台帳</h1>
  <div class="season" id="season-name"></div>
</div>
<div class="main">
  <div class="grid">
    <a class="card order" href="order_input.php">
      <div class="icon">📥</div>
      <div class="label">用品受注入力</div>
    </a>
    <a class="card ledger" href="summary.php">
      <div class="icon">📊</div>
      <div class="label">用品管理台帳</div>
    </a>
    <a class="card master" href="master.php">
      <div class="icon">⚙️</div>
      <div class="label">商品登録管理</div>
    </a>
  </div>
</div>
<div class="footer">
  <a href="initial_stock_input.php">開始時在庫の一括入力</a>
</div>
<script>
fetch('../api/get_season.php')
  .then(r => r.json())
  .then(d => {
    if (d.ok && d.season) {
      document.getElementById('season-name').textContent = d.season.name;
    }
  })
  .catch(() => {});
</script>
</body>
</html>