<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Class DichVuDarkService - Tích hợp API Tên Miền & Dịch Vụ DichVuDark.vip
 * Phiên bản: API Protocol v2.0
 */
class DichVuDarkService
{
    private $baseUrl = 'https://dichvudark.vip';
    private $token;

    public function __construct($token = null)
    {
        $tok = $token;
        if (empty($tok)) {
            $tok = config_get('dichvudark_api_token', '');
            if (empty($tok)) {
                try {
                    $dbConfig = \App\Models\Config::where('key', 'dichvudark_api_token')->first();
                    if ($dbConfig && !empty($dbConfig->value)) {
                        $tok = $dbConfig->value;
                    }
                } catch (\Throwable $e) {
                    // Ignore DB exception
                }
            }
        }
        $this->setToken($tok);
    }

    public function setToken($token)
    {
        $this->token = preg_replace('/^Bearer\s+/i', '', trim((string)$token));
    }

    public function getToken()
    {
        return $this->token;
    }

    /**
     * Gửi yêu cầu HTTP đến DichVuDark.vip
     */
    public function request($endpoint, $method = 'GET', $data = [], $customToken = null)
    {
        $token = $customToken ? preg_replace('/^Bearer\s+/i', '', trim((string)$customToken)) : $this->token;
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Accept: application/json',
            'User-Agent: DichVuDark-Client/2.0'
        ];

        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init();

        if ($method === 'GET' && !empty($data)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            Log::error('DichVuDark API cURL Error: ' . $curlErr, ['endpoint' => $endpoint]);
            return [
                'status'  => 'error',
                'message' => 'Không thể kết nối đến máy chủ DichVuDark.vip: ' . $curlErr,
                'code'    => 500
            ];
        }

        $result = json_decode($response, true);
        if ($result === null) {
            Log::error('DichVuDark Invalid JSON Response: ' . $response, ['httpCode' => $httpCode]);
            return [
                'status'  => 'error',
                'message' => 'Phản hồi không hợp lệ từ DichVuDark (HTTP ' . $httpCode . ')',
                'code'    => $httpCode,
                'raw'     => $response
            ];
        }

        return $result;
    }

    /**
     * 1. Kiểm tra khả dụng tên miền & tra cứu giá (Public API)
     */
    public function checkDomain($domain)
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');

        return $this->request('/api/domain/check', 'GET', ['domain' => $domain]);
    }

    /**
     * 2. Lấy bảng giá toàn bộ TLDs
     */
    public function getPricing()
    {
        return $this->request('/api/domain/pricing', 'GET');
    }

    /**
     * 3. Đặt mua / Đăng ký tên miền tự động
     */
    public function buyDomain($domain, $period = 1, $ns1 = null, $ns2 = null)
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');

        $payload = [
            'domain' => $domain,
            'period' => (int)$period,
            'privacy_protection' => 1
        ];

        if (!empty($ns1)) $payload['nameserver_1'] = $ns1;
        if (!empty($ns2)) $payload['nameserver_2'] = $ns2;

        return $this->request('/api/domain/buy', 'POST', $payload);
    }

    /**
     * 4. Danh sách tên miền đã sở hữu
     */
    public function listDomains($page = 1, $limit = 20, $search = '')
    {
        $params = ['page' => (int)$page, 'limit' => (int)$limit];
        if (!empty($search)) $params['search'] = $search;

        return $this->request('/api/domain/list', 'GET', $params);
    }

    /**
     * 5. Cập nhật Nameservers
     */
    public function updateNameservers($domain, $ns1, $ns2)
    {
        return $this->request('/api/domain/nameservers', 'POST', [
            'domain' => $domain,
            'nameserver_1' => $ns1,
            'nameserver_2' => $ns2
        ]);
    }

    /**
     * 6. Chi tiết tên miền & DNS
     */
    public function getDetails($domain)
    {
        return $this->request('/api/domain/details', 'GET', ['domain' => $domain]);
    }

    /**
     * 7. Kiểm tra kết nối API Token DichVuDark
     */
    public function testConnection($token = null)
    {
        $testToken = $token ?: $this->token;
        if (empty($testToken)) {
            return [
                'status'  => 'error',
                'message' => 'Vui lòng cung cấp API Token của DichVuDark.vip!'
            ];
        }

        $res = $this->request('/api/domain/list?limit=1', 'GET', [], $testToken);

        if (isset($res['status']) && $res['status'] === 'success') {
            return [
                'status'  => 'success',
                'message' => 'Kết nối thành công đến API Tên miền DichVuDark.vip!',
                'data'    => $res
            ];
        }

        $msg = $res['message'] ?? ($res['msg'] ?? 'Kết nối thất bại hoặc API Token không chính xác!');
        return [
            'status'  => 'error',
            'message' => $msg
        ];
    }
}
