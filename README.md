# CronExpansion

**CronExpansion** は JSON で定義したスケジュールを読み込み、
一致した時間にコマンドを実行する **軽量な PHP cron 実行エンジン**です。

Linux の `cron` のようなスケジュール指定を **PHPのみで処理**できます。

---

# Requirements

* PHP 7.4+
* CLI実行環境

---

# Installation

プロジェクトに以下のファイルを配置します。

```
project/
 ├ CronExpansion.php
 └ crontab.json
```

---

# Usage

CLIから実行します。

```bash
php CronExpansion.php
```

またはLinux cronに登録します。

```bash
* * * * * php /path/to/CronExpansion.php
```

---

# crontab.json

スケジュールは JSON で定義します。

```json
[
  {
    "m": "*",
    "d": "*",
    "H": "*",
    "i": "*/5",
    "w": [1,1,1,1,1,1,1],
    "command": "php job.php"
  }
]
```

---

# Schedule Fields

| Field   | Description        | Range |
| ------- | ------------------ | ----- |
| m       | month              | 1–12  |
| d       | day                | 1–31  |
| H       | hour               | 0–23  |
| i       | minute             | 0–59  |
| w       | weekday flag array | 0–6   |
| command | command to execute | shell |

---

# Weekday (`w`)

曜日は **配列形式のフラグ**で指定します。

インデックスは `DateTime::format('w')` に対応します。

| Index | Day       |
| ----- | --------- |
| 0     | Sunday    |
| 1     | Monday    |
| 2     | Tuesday   |
| 3     | Wednesday |
| 4     | Thursday  |
| 5     | Friday    |
| 6     | Saturday  |

---

## Examples

### Every day

```json
"w":[1,1,1,1,1,1,1]
```

### Weekdays

```json
"w":[0,1,1,1,1,1,0]
```

### Weekend

```json
"w":[1,0,0,0,0,0,1]
```

---

# Supported Cron Syntax

以下の cron 式をサポートします。

---

## Any

```
*
```

すべての値に一致

---

## Step

```
*/5
```

例

```
*/10
```

---

## Range

```
1-5
```

---

## Range with Step

```
1-10/2
```

---

## Single Value

```
5
```

---

## Multiple Values

```
1,5,10
```

---

## Mixed Expressions

```
1,5-10,*/15
```

---

# Example Schedules

### Every 5 minutes

```json
{
  "m":"*",
  "d":"*",
  "H":"*",
  "i":"*/5",
  "w":[1,1,1,1,1,1,1],
  "command":"php job.php"
}
```

---

### Every weekday at 03:30

```json
{
  "m":"*",
  "d":"*",
  "H":"3",
  "i":"30",
  "w":[0,1,1,1,1,1,0],
  "command":"php report.php"
}
```

---

### Every Sunday at 02:00

```json
{
  "m":"*",
  "d":"*",
  "H":"2",
  "i":"0",
  "w":[1,0,0,0,0,0,0],
  "command":"php weekly.php"
}
```

---

# Execution Behavior

コマンドは以下の形式で **バックグラウンド実行**されます。

```
command > /dev/null 2>&1 &
```

意味

| 処理     | 内容    |
| ------ | ----- |
| stdout | 破棄    |
| stderr | 破棄    |
| &      | 非同期実行 |

---

# How It Works

```
crontab.json
     ↓
JSON読み込み
     ↓
現在日時取得
     ↓
cron式と比較
     ↓
一致した場合 command 実行
```

---

# Class Structure

```
CronExpansion
 ├ __construct()
 ├ run()
 └ cronMatch()
```

---

## run()

現在時刻とスケジュールを比較し、
一致した場合コマンドを実行します。

---

## cronMatch()

cron式を解析し一致判定を行います。

対応

* `*`
* `*/5`
* `1-5`
* `1-5/2`
* `1,5,10`

---

# License

MIT License

---

