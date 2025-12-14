# 貢獻指南

感謝您有興趣為 `carllee1983/tappay-payment-backend` 做出貢獻！我們歡迎社群的貢獻。

[English](./CONTRIBUTING.md) | 繁體中文

## 目錄

- [行為準則](#行為準則)
- [如何貢獻](#如何貢獻)
- [開發環境設定](#開發環境設定)
- [開發流程](#開發流程)
- [程式碼規範](#程式碼規範)
- [提交規範](#提交規範)
- [Pull Request 流程](#pull-request-流程)
- [測試](#測試)
- [文件](#文件)

## 行為準則

本專案遵循行為準則，所有貢獻者都應遵守。請在所有互動中保持尊重和建設性。

## 如何貢獻

### 回報 Bug

在建立 Bug 報告之前，請先檢查現有的 issue 以避免重複。建立 Bug 報告時，請包含：

- **清晰的標題**：簡要描述問題
- **詳細描述**：問題的詳細說明
- **重現步驟**：逐步說明
- **預期行為**：您預期會發生什麼
- **實際行為**：實際發生了什麼
- **環境**：作業系統、PHP 版本、套件版本
- **程式碼範例**：重現問題的最小程式碼（如適用）

### 功能建議

功能建議透過 GitHub issues 追蹤。建立功能建議時：

- **使用清晰且具描述性的標題**
- **提供詳細描述**
- **解釋為什麼這個功能會有用**
- **列出您考慮過的替代方案**

### Pull Requests

我們積極歡迎您的 pull requests：

1. Fork 專案並從 `main` 建立您的分支
2. 如果您新增了需要測試的程式碼，請新增測試
3. 如果您變更了 API，請更新文件
4. 確保測試套件通過
5. 確保您的程式碼遵循程式碼規範
6. 提交 pull request

## 開發環境設定

### 先決條件

- [PHP](https://www.php.net/) 8.1 或更新版本
- [Composer](https://getcomposer.org/)
- [Git](https://git-scm.com/)
- 程式碼編輯器（推薦 VS Code 或 PhpStorm）

### 設定步驟

1. **Fork 並 clone 專案**

```bash
git clone https://github.com/YOUR_USERNAME/tappay-backend-payment-php.git
cd tappay-backend-payment-php
```

2. **安裝依賴**

```bash
composer install
```

3. **執行測試確認一切正常**

```bash
composer test
```

## 開發流程

### 1. 建立功能分支

```bash
git checkout -b feature/your-feature-name
# 或
git checkout -b fix/your-bug-fix
```

### 2. 進行變更

- 撰寫乾淨、可讀的程式碼
- 遵循程式碼規範
- 為新功能新增測試
- 根據需要更新文件

### 3. 測試您的變更

```bash
# 執行測試
composer test

# 執行測試並產生覆蓋率報告
./vendor/bin/phpunit --coverage-text
```

### 4. 提交您的變更

```bash
git add .
git commit -m "type: description"
```

請參閱[提交規範](#提交規範)了解提交訊息格式。

### 5. 推送並建立 Pull Request

```bash
git push origin feature/your-feature-name
```

然後在 GitHub 上建立 Pull Request。

## 程式碼規範

### PHP

- 使用 PHP 8.1+ 功能
- 啟用嚴格類型（`declare(strict_types=1)`）
- 遵循 PSR-12 程式碼規範
- 使用 PHPDoc 註解記錄公開 API

### 程式碼風格

我們遵循 PSR-12 和以下慣例：

- **縮排**：4 個空格
- **大括號**：類別和方法使用同行大括號
- **引號**：字串使用單引號
- **命名慣例**：
  - 類別使用 `PascalCase`
  - 方法和變數使用 `camelCase`
  - 常數使用 `UPPER_CASE`

### 範例

```php
<?php

declare(strict_types=1);

namespace TapPay\Payment;

/**
 * 建立 TapPay API 的付款請求。
 *
 * @param string $prime 前端的 prime token
 * @param int $amount 付款金額
 *
 * @throws \InvalidArgumentException 當參數無效時
 */
public function createPayment(string $prime, int $amount): PaymentResponse
{
    if ($amount <= 0) {
        throw new \InvalidArgumentException('金額必須為正數');
    }
    // 實作
}
```

## 提交規範

我們遵循 [Conventional Commits](https://www.conventionalcommits.org/) 規範。

### 提交訊息格式

```
<type>(<scope>): <subject>

<body>

<footer>
```

### 類型

- **feat**：新功能
- **fix**：Bug 修復
- **docs**：僅文件變更
- **style**：程式碼風格變更（格式化、分號等）
- **refactor**：程式碼重構（既不修復 bug 也不新增功能）
- **perf**：效能改進
- **test**：新增或更新測試
- **chore**：建置流程或輔助工具的變更

### 範例

```bash
# 新功能
git commit -m "feat: 新增分期付款支援"

# Bug 修復
git commit -m "fix: 修正退款金額驗證"

# 文件
git commit -m "docs: 更新 payByToken API 參考"

# 重構
git commit -m "refactor: 抽取 HTTP 客戶端邏輯"
```

## Pull Request 流程

1. **更新文件**：確保 README 和 API 文件已更新
2. **新增測試**：所有新功能必須包含測試
3. **通過所有檢查**：確保測試通過
4. **清理提交歷史**：如需要可進行 squash 或 rebase
5. **參考 Issues**：在 PR 說明中連結相關 issues
6. **請求審查**：標記維護者進行審查

### PR 標題格式

遵循與提交訊息相同的格式：

```
feat: 新增解析用的工具函式
fix: 解決 API 逾時處理問題
docs: 改善 README 範例
```

## 測試

### 撰寫測試

- 將測試放在 `tests/` 目錄
- 使用 PHPUnit 進行測試
- 追求高程式碼覆蓋率
- 測試邊界情況和錯誤條件

### 執行測試

```bash
# 執行所有測試
composer test

# 執行特定測試檔案
./vendor/bin/phpunit tests/TapPayClientTest.php

# 執行並產生覆蓋率報告
./vendor/bin/phpunit --coverage-text
```

## 文件

### README 更新

- 保持 README.md（英文）和 README_ZH.md（繁體中文）同步
- 為新功能包含程式碼範例
- 更新 API 參考章節

### 程式碼文件

- 使用 PHPDoc 記錄公開 API
- 記錄參數、回傳類型和例外
- 在註解中包含使用範例

## 有問題嗎？

如果您對貢獻有疑問，歡迎：

- 開啟 [GitHub Discussion](https://github.com/CarlLee1983/tappay-backend-payment-php/discussions)
- 建立帶有「question」標籤的 issue

## 授權

透過貢獻，您同意您的貢獻將依據 MIT 授權條款進行授權。

---

感謝您為 `carllee1983/tappay-payment-backend` 做出貢獻！🎉
