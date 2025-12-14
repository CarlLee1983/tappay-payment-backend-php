# TapPay Payment PHP 客戶端

[![CI](https://github.com/CarlLee1983/tappay-backend-payment-php/actions/workflows/ci.yml/badge.svg)](https://github.com/CarlLee1983/tappay-backend-payment-php/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

[English](./README.md) | 繁體中文

一個型別安全、符合 PSR-4 規範的 PHP 函式庫，用於 TapPay 後端支付 API。支援 Pay by Prime、Pay by Token、退款和交易查詢操作，並提供可注入的 HTTP 客戶端以便於測試。

## 特色

- 🚀 **PHP 8.1+** 嚴格型別與唯讀屬性
- 📦 **PSR-4 自動載入** 使用 `TapPay\Payment` 命名空間
- 🔌 **可注入的 HTTP 客戶端** 便於模擬和測試
- ✅ **型別安全的 DTO** 用於請求和回應
- 🛡️ **完整的錯誤處理** 自訂例外類別
- 📝 **完整的 PHPDoc 文件** 支援 IDE 自動完成

## 系統需求

- PHP 8.1 或更高版本
- ext-json
- （選用）使用 `CurlHttpClient` 需要 ext-curl

## 安裝

```bash
composer require carllee1983/tappay-payment-backend
```

## 快速開始

### 基本設定

```php
use TapPay\Payment\ClientConfig;
use TapPay\Payment\TapPayClient;

// Sandbox 環境（預設）
$client = new TapPayClient(new ClientConfig(
    partnerKey: getenv('TAPPAY_PARTNER_KEY'),
    merchantId: getenv('TAPPAY_MERCHANT_ID')
));

// 正式環境
$client = new TapPayClient(new ClientConfig(
    partnerKey: getenv('TAPPAY_PARTNER_KEY'),
    merchantId: getenv('TAPPAY_MERCHANT_ID'),
    baseUri: 'https://prod.tappaysdk.com'
));
```

### 選用：使用 cURL HTTP 客戶端

```php
use TapPay\Payment\ClientConfig;
use TapPay\Payment\TapPayClient;
use TapPay\Payment\Http\CurlHttpClient;

$client = new TapPayClient(
    new ClientConfig(
        partnerKey: getenv('TAPPAY_PARTNER_KEY'),
        merchantId: getenv('TAPPAY_MERCHANT_ID')
    ),
    new CurlHttpClient()
);
```

### 選用：使用 PSR-18 HTTP 客戶端

```php
use TapPay\Payment\ClientConfig;
use TapPay\Payment\TapPayClient;
use TapPay\Payment\Http\Psr18HttpClientAdapter;

// 需要 PSR-18 + PSR-17 + PSR-7 實作，例如：
// composer require psr/http-client psr/http-factory nyholm/psr7

$psr18Client = /* \Psr\Http\Client\ClientInterface */;
$requestFactory = /* \Psr\Http\Message\RequestFactoryInterface */;
$streamFactory = /* \Psr\Http\Message\StreamFactoryInterface */;

$client = new TapPayClient(
    new ClientConfig(
        partnerKey: getenv('TAPPAY_PARTNER_KEY'),
        merchantId: getenv('TAPPAY_MERCHANT_ID')
    ),
    new Psr18HttpClientAdapter($psr18Client, $requestFactory, $streamFactory)
);
```

## HTTP 客戶端選項

- 預設：`TapPay\Payment\Http\NativeHttpClient`（stream-based，不需要額外 extension）
- 選用：`TapPay\Payment\Http\CurlHttpClient`（需要 `ext-curl`）
- 選用：`TapPay\Payment\Http\Psr18HttpClientAdapter`（需要 PSR-18 + PSR-17 + PSR-7 實作）

### Pay by Prime

使用前端 TapPay SDK 取得的 Prime token 進行付款：

```php
use TapPay\Payment\Dto\Money;
use TapPay\Payment\Dto\PrimePaymentRequest;

$response = $client->payByPrime(new PrimePaymentRequest(
    prime: 'prime_from_frontend',
    amount: 100,
    currency: 'TWD',
    details: '訂單 #12345',
    orderNumber: 'ORDER-12345',
    cardholder: [
        'phone_number' => '+886912345678',
        'name' => '測試用戶',
        'email' => 'test@example.com',
    ],
    remember: true  // 儲存卡片供未來付款使用
));

if ($response->isSuccess()) {
    // 儲存 rec_trade_id 供退款或查詢使用
    $recTradeId = $response->recTradeId;
    
    // 如果 remember=true，儲存卡片 token 供 Pay by Token 使用
    $cardKey = $response->cardSecret['card_key'] ?? null;
    $cardToken = $response->cardSecret['card_token'] ?? null;
}
```

小提示：若是非 TWD 幣別，建議用 `Money` 以避免自行換算：

```php
$response = $client->payByPrime(new PrimePaymentRequest(
    prime: 'prime_from_frontend',
    amount: Money::USD(10.99),
    details: '訂單 #12345'
));
```

### Pay by Token

使用已儲存的卡片 token 進行付款：

```php
use TapPay\Payment\Dto\TokenPaymentRequest;

$response = $client->payByToken(new TokenPaymentRequest(
    cardKey: $savedCardKey,
    cardToken: $savedCardToken,
    amount: 200,
    currency: 'TWD',
    details: '訂閱續約',
    orderNumber: 'SUB-12345'
));

if ($response->isSuccess()) {
    echo "付款成功: " . $response->recTradeId;
}
```

### 退款

處理全額或部分退款：

```php
use TapPay\Payment\Dto\RefundRequest;

// 全額退款
$response = $client->refund(new RefundRequest(
    recTradeId: $transactionId
));

// 部分退款
$response = $client->refund(new RefundRequest(
    recTradeId: $transactionId,
    amount: 50  // 從原始金額中退款 50
));

if ($response->isSuccess()) {
    echo "退款成功: " . $response->refundId;
}
```

### 查詢交易紀錄

查詢交易紀錄：

```php
use TapPay\Payment\Dto\RecordQueryRequest;

$response = $client->queryRecords(new RecordQueryRequest(
    recordsPerPage: 50,
    page: 0,
    filters: [
        'time' => [
            'start_time' => strtotime('-30 days') * 1000,
            'end_time' => time() * 1000,
        ],
    ],
    orderBy: [
        'attribute' => 'time',
        'is_descending' => true,
    ]
));

foreach ($response->tradeRecords as $record) {
    echo $record['rec_trade_id'] . ': ' . $record['amount'] . "\n";
}
```

## API 參考

### TapPayClient

| 方法 | 說明 |
|------|------|
| `payByPrime(PrimePaymentRequest $request)` | 使用 Prime token 處理付款 |
| `payByToken(TokenPaymentRequest $request)` | 使用已儲存的卡片 token 處理付款 |
| `refund(RefundRequest $request)` | 處理全額或部分退款 |
| `queryRecords(RecordQueryRequest $request)` | 查詢交易紀錄 |

### 請求 DTO

#### PrimePaymentRequest

| 屬性 | 型別 | 必填 | 說明 |
|------|------|------|------|
| `prime` | string | 是 | 前端取得的 Prime token |
| `amount` | int\|Money | 是 | 付款金額（使用 `Money` 時會忽略 `currency`） |
| `currency` | string | 否 | 幣別代碼（預設：TWD） |
| `details` | string | 否 | 交易說明 |
| `orderNumber` | string | 否 | 商家訂單編號 |
| `bankTransactionId` | string | 否 | 銀行端訂單編號 |
| `cardholder` | array | 否 | 持卡人資訊 |
| `remember` | bool | 否 | 儲存卡片供未來使用 |
| `instalment` | int | 否 | 分期期數 |
| `delayCaptureInDays` | int | 否 | 延後請款天數 |
| `threeDomainSecure` | bool | 否 | 啟用 3D 驗證 |
| `resultUrl` | array | 否 | 3D 驗證結果 URL |

#### TokenPaymentRequest

| 屬性 | 型別 | 必填 | 說明 |
|------|------|------|------|
| `cardKey` | string | 是 | 先前付款取得的 card key |
| `cardToken` | string | 是 | 先前付款取得的 card token |
| `amount` | int\|Money | 是 | 付款金額（使用 `Money` 時會忽略 `currency`） |
| `currency` | string | 否 | 幣別代碼（預設：TWD） |
| `details` | string | 否 | 交易說明 |
| `orderNumber` | string | 否 | 商家訂單編號 |
| `threeDomainSecure` | bool | 否 | 啟用 3D 驗證 |
| `resultUrl` | array | 否 | 3D 驗證結果 URL |

### 例外類別

| 例外 | 說明 |
|------|------|
| `TapPayException` | 基礎例外類別 |
| `SignatureException` | 無效的 API 金鑰（401/403） |
| `ValidationException` | 輸入驗證錯誤 |
| `HttpException` | HTTP 層級錯誤 |

## 錯誤處理

```php
use TapPay\Payment\Exception\SignatureException;
use TapPay\Payment\Exception\ValidationException;
use TapPay\Payment\Exception\HttpException;

try {
    $response = $client->payByPrime($request);
} catch (SignatureException $e) {
    // 無效的 partner key
    error_log('API 金鑰錯誤: ' . $e->getMessage());
} catch (ValidationException $e) {
    // 缺少必填欄位
    error_log('驗證錯誤: ' . $e->getMessage());
} catch (HttpException $e) {
    // TapPay 服務無法使用
    error_log('HTTP 錯誤: ' . $e->getMessage());
}
```

## 測試

此函式庫包含可注入的 HTTP 客戶端介面，便於測試：

```php
use TapPay\Payment\Http\HttpClientInterface;
use TapPay\Payment\Http\HttpResponse;

// 建立模擬 HTTP 客戶端
$mockClient = new class implements HttpClientInterface {
    public function request(
        string $method,
        string $url,
        array $headers = [],
        array $body = []
    ): HttpResponse {
        return new HttpResponse(200, json_encode([
            'status' => 0,
            'msg' => 'success',
            'rec_trade_id' => 'test_trade_id',
        ]));
    }
};

// 注入模擬客戶端進行測試
$client = new TapPayClient($config, $mockClient);
```

## 執行測試

```bash
composer install
composer test
```

## 文件

函式庫 API 概覽，請參閱 [doc/API/README.md](./doc/API/README.md)。

## 貢獻

詳情請參閱 [CONTRIBUTING_ZH.md](./CONTRIBUTING_ZH.md)。

## 安全性

關於安全漏洞，請參閱 [SECURITY.md](./SECURITY.md)。

## 授權

本專案採用 MIT 授權條款 - 詳情請參閱 [LICENSE](./LICENSE) 檔案。

## 連結

- [TapPay 官方文件](https://docs.tappaysdk.com/tutorial/zh/back.html)
- [GitHub 儲存庫](https://github.com/CarlLee1983/tappay-backend-payment-php)
- [Packagist](https://packagist.org/packages/carllee1983/tappay-payment-backend)
