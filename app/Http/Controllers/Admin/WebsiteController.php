<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteOrder;
use App\Services\DichVuDarkService;
use App\Services\TheGioiDevService;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    protected $api;
    protected $domainApi;

    public function __construct(TheGioiDevService $api, DichVuDarkService $domainApi)
    {
        $this->api = $api;
        $this->domainApi = $domainApi;
    }

    /**
     * Danh sách các mẫu website mở bán từ TheGioiDev
     */
    public function index(Request $request)
    {
        $markup = (float)config_get('thegioidev_website_markup', 0);
        $search = $request->get('search', '');
        $page = (int)$request->get('page', 1);

        $params = [
            'page' => $page,
            'limit' => 20,
            'withMeta' => 'true'
        ];
        if (!empty($search)) $params['search'] = $search;

        $apiRes = $this->api->getWebsites($params);
        $templates = [];
        $pagination = ['page' => 1, 'limit' => 20, 'total' => 0, 'totalPages' => 1];

        $isSuccess = (isset($apiRes['error']) && ($apiRes['error'] === 0 || $apiRes['error'] === '0' || $apiRes['error'] === false))
            || (isset($apiRes['status']) && strtolower($apiRes['status']) === 'success');

        if ($isSuccess && isset($apiRes['data'])) {
            $data = $apiRes['data'];
            $rawItems = $data['items'] ?? (is_array($data) ? $data : []);
            $pagination = $data['pagination'] ?? $pagination;

            foreach ($rawItems as $item) {
                if (!is_array($item)) continue;
                $pricingInfo = \App\Http\Controllers\User\WebsiteController::extractPricing($item, $markup);
                $item['calculatedPricing'] = $pricingInfo['pricing'];
                $item['displayPrice'] = $pricingInfo['displayPrice'];
                $templates[] = $item;
            }
        }

        return view('admin.websites.index', [
            'title' => 'Danh sách mẫu Website (TheGioiDev)',
            'templates' => $templates,
            'pagination' => $pagination,
            'markup' => $markup,
            'apiConnected' => !empty(config_get('thegioidev_api_token')),
            'apiError' => $apiRes['msg'] ?? ($apiRes['message'] ?? null)
        ]);
    }

    /**
     * Quản lý đơn hàng Website toàn hệ thống
     */
    public function orders(Request $request)
    {
        $query = WebsiteOrder::with('user')->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('username', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int)$request->get('per_page', 15);
        $orders = $query->paginate($perPage)->appends($request->all());

        return view('admin.websites.orders', [
            'title' => 'Quản lý Đơn hàng Website',
            'orders' => $orders
        ]);
    }

    /**
     * Cài đặt & Cấu hình API TheGioiDev & DichVuDark Domain
     */
    public function settings()
    {
        return view('admin.websites.settings', [
            'title' => 'Cấu hình API Website & Tên Miền',
            'apiToken' => config_get('thegioidev_api_token', ''),
            'markup' => config_get('thegioidev_website_markup', '0'),
            'status' => config_get('thegioidev_website_status', '1'),
            'notice' => config_get('thegioidev_website_notice', 'Dịch vụ tạo website tự động với máy chủ cấu hình cao, bảo mật Cloudflare và băng thông không giới hạn.'),
            'dichvudarkToken' => config_get('dichvudark_api_token', ''),
            'domainMarkup' => config_get('dichvudark_domain_markup', '10')
        ]);
    }

    /**
     * Cập nhật cấu hình
     */
    public function updateSettings(Request $request)
    {
        // 1. TheGioiDev API
        $token = trim($request->input('thegioidev_api_token', ''));
        $token = preg_replace('/^Bearer\s+/i', '', $token);

        config_set('thegioidev_api_token', $token);
        config_set('thegioidev_website_markup', (float)$request->input('thegioidev_website_markup', 0));
        config_set('thegioidev_website_status', 1); // Luôn mở bán, không cho phép bảo trì
        config_set('thegioidev_website_notice', $request->input('thegioidev_website_notice', ''));

        // 2. DichVuDark Domain API
        $dichvudarkToken = trim($request->input('dichvudark_api_token', ''));
        $dichvudarkToken = preg_replace('/^Bearer\s+/i', '', $dichvudarkToken);
        config_set('dichvudark_api_token', $dichvudarkToken);
        config_set('dichvudark_domain_markup', (float)$request->input('dichvudark_domain_markup', 0));

        return redirect()->route('admin.websites.settings')->with('success', 'Cập nhật cấu hình API TheGioiDev & DichVuDark thành công!');
    }

    /**
     * Ajax Kiểm tra kết nối API Token TheGioiDev
     */
    public function testConnection(Request $request)
    {
        $token = $request->input('token') ?: config_get('thegioidev_api_token');
        $res = $this->api->testConnection($token);

        return response()->json($res);
    }

    /**
     * Ajax Kiểm tra kết nối API Token DichVuDark.vip
     */
    public function testDichVuDark(Request $request)
    {
        $token = $request->input('token') ?: config_get('dichvudark_api_token');
        $res = $this->domainApi->testConnection($token);

        return response()->json($res);
    }

    /**
     * Admin gọi Check NS
     */
    public function adminCheckNs($id)
    {
        $order = WebsiteOrder::findOrFail($id);

        if (empty($order->order_id)) {
            return response()->json(['status' => 'error', 'message' => 'Đơn hàng không có mã Order ID!'], 400);
        }

        $res = $this->api->checkWebsiteNs($order->order_id);

        // Đồng bộ chi tiết mới nhất
        $detailRes = $this->api->getWebsiteOrderDetail($order->order_id);
        if (isset($detailRes['data'])) {
            $apiData = $detailRes['data'];
            $order->update([
                'status' => $apiData['status'] ?? $order->status,
                'cloudflare_nameservers' => $apiData['provisioning']['cloudflareNameServers'] ?? $order->cloudflare_nameservers,
                'meta_data' => $apiData
            ]);
        }

        return response()->json([
            'status' => (isset($res['error']) && $res['error'] === 0) ? 'success' : 'error',
            'message' => $res['message'] ?? ($res['msg'] ?? 'Đã gửi yêu cầu kiểm tra Nameserver!'),
            'order' => $order
        ]);
    }

    /**
     * Admin gọi Thử lại triển khai
     */
    public function adminRetry($id)
    {
        $order = WebsiteOrder::findOrFail($id);

        if (empty($order->order_id)) {
            return response()->json(['status' => 'error', 'message' => 'Đơn hàng không có mã Order ID!'], 400);
        }

        $res = $this->api->retryWebsiteDeploy($order->order_id);

        if (isset($res['error']) && $res['error'] === 0) {
            $order->update(['status' => 'processing']);
            return response()->json(['status' => 'success', 'message' => 'Đã gửi yêu cầu thử lại triển khai!']);
        }

        return response()->json([
            'status' => 'error',
            'message' => $res['msg'] ?? ($res['message'] ?? 'Thử lại thất bại!')
        ], 400);
    }
}
