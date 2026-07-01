# セットアップ手順

## 1. PhpSpreadsheetのインストール

サーバー（さくらインターネット）上でプロジェクトルートに移動し、以下を実行してください。
Composerが入っていない場合は先にインストールが必要です。

```bash
cd /path/to/season-order-ledger
composer require phpoffice/phpspreadsheet
```

これで `vendor/` フォルダが作成され、Excel出力機能（api/export_excel.php）が動作するようになります。

## 2. DB接続情報の設定

`config/db.php` の以下の項目を、さくらインターネットの実際のDB情報に書き換えてください。

```php
const DB_USER = 'your_db_user';
const DB_PASS = 'your_db_password';
```

## 3. DBスキーマの投入

`sql/schema.sql` を実行してテーブルを作成してください。

```bash
mysql -u [DBユーザー名] -p [DB名] < sql/schema.sql
```

（さくらインターネットの場合はphpMyAdmin等から実行する方法でも構いません）

## 4. ディレクトリ構成

```
season-order-ledger/
├── config/db.php          ← DB接続設定
├── includes/functions.php  ← 共通関数
├── public/                 ← ブラウザから直接アクセスする画面
│   ├── order_input.php         ① 受注入力
│   ├── summary.php             ② 商品別受注集計
│   ├── product_detail.php      ③ 商品詳細
│   └── initial_stock_input.php  開始時在庫の一括入力
├── api/                    ← 内部API（fetchで呼び出される）
│   ├── get_products.php
│   ├── save_order.php
│   ├── get_summary.php
│   ├── get_product_detail.php
│   ├── update_order.php
│   ├── add_purchase_order.php
│   ├── save_initial_stocks.php
│   └── export_excel.php
├── sql/schema.sql
└── vendor/                 ← composerでインストールされる（要セットアップ）
```

## 5. アクセスURL例

サーバーにアップロード後、以下のようなURLでアクセスできます（ディレクトリ名は実際の配置に合わせてください）。

- 受注入力: `https://hoikukyouhan.co.jp/season-order-ledger/public/order_input.php`
- 商品別受注集計: `https://hoikukyouhan.co.jp/season-order-ledger/public/summary.php`
- 開始時在庫の一括入力: `https://hoikukyouhan.co.jp/season-order-ledger/public/initial_stock_input.php`

商品詳細ページは集計画面の商品名をクリックすることで遷移します。
