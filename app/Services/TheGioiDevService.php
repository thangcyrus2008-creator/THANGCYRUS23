<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Class TheGioiDevService - Chuẩn kết nối API TheGioiDev 2026
 * Hỗ trợ các dịch vụ Website Marketplace, Cronjob, Hosting
 */
class TheGioiDevService
{
    private $websiteBaseUrl = 'https://thegioidev.com/api/agency/websites';
    private $cronBaseUrl = 'https://thegioidev.com/api/agency/cronjobs';
    private $hostingBaseUrl = 'https://thegioidev.com/api/v1/hosting';
    private $apiToken;

    public function __construct($apiToken = null)
    {
        $token = $apiToken;
        if (empty($token)) {
            $token = config_get('thegioidev_api_token', '');
            if (empty($token)) {
                try {
                    $dbConfig = \App\Models\Config::where('key', 'thegioidev_api_token')->first();
                    if ($dbConfig && !empty($dbConfig->value)) {
                        $token = $dbConfig->value;
                    }
                } catch (\Throwable $e) {
                    // Ignore DB exception
                }
            }
        }
        $this->setToken($token);
    }

    /**
     * Thiết lập và làm sạch API Token
     * Tự động loại bỏ tiền tố Bearer và khoảng trắng thừa theo khuyến nghị
     */
    public function setToken($token)
    {
        $this->apiToken = preg_replace('/^Bearer\s+/i', '', trim((string)$token));
    }

    public function getToken()
    {
        return $this->apiToken;
    }

    /**
     * Gửi HTTP Request tới TheGioiDev qua cURL
     * Lưu ý an toàn: CHỈ gửi duy nhất Authorization: Bearer <token>
     * Tuyệt đối không thêm auth-token hay api-key
     */
    public function request($url, $method = 'GET', $data = [], $customToken = null)
    {
        $token = $customToken ? preg_replace('/^Bearer\s+/i', '', trim((string)$customToken)) : $this->apiToken;

        if (empty($token)) {
            return [
                'error'  => 1,
                'status' => 'error',
                'msg'    => 'Chưa cấu hình API Token TheGioiDev trong cài đặt hệ thống!',
                'code'   => 401
            ];
        }

        $ch = curl_init();
        $requestUrl = $url;

        if ($method === 'GET' && !empty($data)) {
            $queryString = http_build_query($data);
            $requestUrl .= (strpos($requestUrl, '?') === false ? '?' : '&') . $queryString;
        }

        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: CMSNT-TheGioiDev-Client/2.0'
        ];

        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } elseif (in_array($method, ['PATCH', 'PUT', 'DELETE'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            Log::error('TheGioiDev API cURL Error: ' . $curlErr, ['url' => $requestUrl]);
            return [
                'error'  => 1,
                'status' => 'error',
                'msg'    => 'Không thể kết nối đến TheGioiDev: ' . $curlErr,
                'code'   => 500
            ];
        }

        $result = json_decode($response, true);
        if ($result === null) {
            Log::error('TheGioiDev API Invalid JSON Response: ' . $response, ['httpCode' => $httpCode, 'url' => $requestUrl]);
            return [
                'error'  => 1,
                'status' => 'error',
                'msg'    => 'Phản hồi không đúng định dạng JSON (Mã HTTP: ' . $httpCode . ')',
                'code'   => $httpCode,
                'raw'    => $response
            ];
        }

        Log::info('TheGioiDev API Response: ' . $requestUrl, [
            'httpCode' => $httpCode,
            'result_sample' => is_array($result) ? array_keys($result) : null
        ]);

        return $result;
    }

    // ==================== CÁC PHƯƠNG THỨC WEBSITE ====================

    /**
     * Lấy danh sách mẫu website mở bán
     */
    public function getWebsites($params = [], $token = null)
    {
        return $this->request($this->websiteBaseUrl, 'GET', $params, $token);
    }

    /**
     * Lấy chi tiết mẫu website theo Slug
     */
    public function getWebsiteDetail($slug, $token = null)
    {
        return $this->request($this->websiteBaseUrl . '/' . urlencode($slug), 'GET', [], $token);
    }

    /**
     * Đặt thuê website mới
     */
    public function buyWebsite($data, $token = null)
    {
        return $this->request($this->websiteBaseUrl . '/buy', 'POST', $data, $token);
    }

    /**
     * Gia hạn website (Mục 5.4 tài liệu thegioidev.md)
     */
    public function renewWebsite($orderId, $months = 1, $token = null)
    {
        $months = max(1, (int)$months);
        $payload = [
            'id'       => (string) $orderId,
            'orderId'  => (string) $orderId,
            'month'    => $months,
            'months'   => $months,
        ];

        // 1. Thử gọi endpoint /renew
        $res = $this->request($this->websiteBaseUrl . '/renew', 'POST', $payload, $token);

        // 2. Fallback sang endpoint /orders/{id}/renew nếu endpoint /renew trả về 404 (theo Mục 5.4 thegioidev.md)
        if ((isset($res['code']) && (int)$res['code'] === 404) 
            || (isset($res['error']) && $res['error'] !== 0 && stripos($res['msg'] ?? ($res['message'] ?? ''), 'not found') !== false)) {
            Log::info("TheGioiDev renew fallback to /orders/{$orderId}/renew");
            $res = $this->request($this->websiteBaseUrl . '/orders/' . urlencode($orderId) . '/renew', 'POST', $payload, $token);
        }

        return $res;
    }

    /**
     * Danh sách đơn hàng website đã thuê
     */
    public function getWebsiteOrders($params = [], $token = null)
    {
        return $this->request($this->websiteBaseUrl . '/orders', 'GET', $params, $token);
    }

    /**
     * Chi tiết đơn hàng website
     */
    public function getWebsiteOrderDetail($orderId, $token = null)
    {
        return $this->request($this->websiteBaseUrl . '/orders/' . urlencode($orderId), 'GET', [], $token);
    }

    /**
     * Kích hoạt kiểm tra NS & Deploy (Check NS)
     */
    public function checkWebsiteNs($orderId, $token = null)
    {
        return $this->request($this->websiteBaseUrl . '/orders/' . urlencode($orderId) . '/check-ns', 'POST', [], $token);
    }

    /**
     * Thử lại triển khai website (Retry Deploy)
     */
    public function retryWebsiteDeploy($orderId, $token = null)
    {
        return $this->request($this->websiteBaseUrl . '/orders/' . urlencode($orderId) . '/retry', 'POST', [], $token);
    }

    /**
     * Kiểm tra tính hợp lệ của API Token
     */
    public function testConnection($token = null)
    {
        $testToken = $token ?: $this->apiToken;
        if (empty($testToken)) {
            return [
                'error'   => 1,
                'status'  => 'error',
                'message' => 'Vui lòng nhập API Token để kiểm tra!'
            ];
        }

        $res = $this->request($this->websiteBaseUrl . '?limit=1', 'GET', [], $testToken);

        $isSuccess = (isset($res['error']) && ($res['error'] === 0 || $res['error'] === '0' || $res['error'] === false))
            || (isset($res['status']) && strtolower($res['status']) === 'success');

        if ($isSuccess) {
            return [
                'error'   => 0,
                'status'  => 'success',
                'message' => 'Kết nối thành công đến API TheGioiDev!',
                'data'    => $res['data'] ?? []
            ];
        }

        $msg = $res['msg'] ?? ($res['message'] ?? 'Kết nối thất bại hoặc Token không chính xác!');
        return [
            'error'   => 1,
            'status'  => 'error',
            'message' => $msg,
            'code'    => $res['code'] ?? null
        ];
    }
}
